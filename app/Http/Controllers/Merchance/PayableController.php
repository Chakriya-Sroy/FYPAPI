<?php

namespace App\Http\Controllers\Merchance;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePayableRequest;
use App\Http\Resources\PayableResource;
use App\Models\Payable;
use App\Models\PayableTransaction;
use App\Models\Supplier;
use App\Traits\HttpResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PayableController extends Controller
{
    use HttpResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $user = Auth::user();
        $payables = $user->payables()->where('isArchive', false)->orderByDesc('created_at')->get();
        // if ($payables->count() == 0) {
        //     return response()->json("There no resource available yet", 200);
        // }
        //return $this->success(['attributes'=>$payables]);
        //return $payables;
        return $this->success(PayableResource::collection($payables));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePayableRequest $request)
    {
        $request->validated($request->all());
        $supplier = Supplier::find($request->supplier_id);
        if (!$supplier) {
            return $this->error('', "Supplier didn't exist");
        }
        if ($supplier->user_id !== Auth::user()->id) {
            return $this->error('', "You don't authorized to create any resource under this supplier", 403);
        }

        $path = null;
        if ($request->file('attachment') != null) {
            $request->file('attachment')->storeAs('public', $request['attachment']->getClientOriginalName());
            $path = $request->file('attachment')->storeAs('', $request['attachment']->getClientOriginalName(), 'spaces');
        }
        // try {
        //     // Store the file locally for testing
        //     $localPath = $request->file('attachment')->storeAs('public', $request->file('attachment')->getClientOriginalName());

        //     // Store the file in DigitalOcean Spaces
        //     $fileName = $request->file('attachment')->getClientOriginalName();
        //     $spacesPath = $request->file('attachment')->storeAs('', $fileName, 'spaces');

        //     if ($spacesPath) {
        //         // File was successfully uploaded to Spaces
        //         Log::info('File successfully uploaded to Spaces.', ['path' => $spacesPath]);
        //         $path = $spacesPath;
        //     } else {
        //         // Failed to upload the file to Spaces
        //         Log::error('Failed to upload file to Spaces.');
        //         $path = false;
        //     }
        // } catch (\Exception $e) {
        //     Log::error('Error uploading file to Spaces: ' . $e->getMessage());
        //     $path = false;
        // }


        $payable = Payable::create([
            'supplier_id' => $request->supplier_id,
            'amount' => $request->amount,
            'remaining' => $request->amount,
            'payment_term' => $request->payment_term,
            'status' => "outstanding",
            'date' => now(),
            'dueDate' => $request->dueDate,
            'attachment' => $request->attachment == '' ? '' : "https://testfyp1.sgp1.cdn.digitaloceanspaces.com/$path",
            'remark' => $request->remark,
            'isArchive'=>false
        ]);
        // // For Storing image

        $transaction = PayableTransaction::create([
            'transaction_type' => "payable",
            'amount' => $payable->amount,
            'payable_id' => $payable->id,
            'payableCreated' => $payable->date,
            'supplier_id' => $payable->supplier_id,
            'transaction_date' => $payable->date,
        ]);
        return $this->success(new PayableResource($payable), 'The payable create successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $payable = Payable::find($id);
        if (!$payable) {
            return $this->error('', 'There no resource exist');
        }
        if ($payable->supplier->user_id !== Auth::user()->id) {
            return $this->error('', 'You not authorized to view this resource');
        }
        return new PayableResource($payable);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(string $id)
    {
        $payable = Payable::find($id);
        if ($payable) {
            $payable->update(['isArchive' => true]);
            return response()->json(['message' => 'Payable added to archive successfully']);
        } else {
            return response()->json(['message' => 'Payable not found'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $supplier = Supplier::find($id);
        if ($supplier) {
            return $this->error('', 'There no resource exist');
        }
        if ($supplier->user_id !== Auth::user()->id) {
            return $this->error('', 'You not authorized to delete this resource');
        }
        $payable = Payable::find($id)->delete();
        return response()->json(null, 200);
    }

    public function upcoming()
    {
        $user = Auth::user();
        $payables = $user->payables;
        $currentDate = Carbon::now('UTC');
        $upcomingPayables = [];
        foreach ($payables as $payable) {
            // Check if the receivable's payment term matches the due date
            if ($payable->status != "fullypaid") {
                if ($payable->payment_term === "equaltodueDate") {
                    $newDueDate = Carbon::parse($payable->dueDate, 'UTC');
                    if ($newDueDate->isAfter($currentDate)) {
                        $daysRemaining = $newDueDate->diffInDays($currentDate);
                        if ($daysRemaining === 0 || $daysRemaining === 1) {
                            $upcomingPayables[] = [
                                'id' => $payable->id,
                                'payableCreated' => $payable->date,
                                'supplier' => $payable->supplier->fullname,
                                'remaining' => $payable->remaining,
                                'status' => $payable->status,
                                'upcoming' => $daysRemaining == 0 ? "Due Today" : "Due Tomorrow"
                            ];
                        }
                    }
                } else {
                    // Calculate the next reminder date
                    $reminderInterval = $payable->payment_term; // 7 or 15 days
                    $nextReminderDate = $currentDate->copy()->addDays($reminderInterval);
                    // Ensure the next reminder date is before the due date
                    if ($nextReminderDate->isBefore($payable->dueDate)) {
                        $daysUntilReminder = $nextReminderDate->diffInDays($currentDate);
                        $upcomingReceivables[] = [
                            'id' => $payable->id,
                            'payableCreated' => $payable->date,
                            'supplier' => $payable->supplier->fullname,
                            'remaining' => $payable->remaining,
                            'status' => $payable->status,
                            'upcoming' => $daysUntilReminder == 1 ? 'Due tomorrow' : ($daysUntilReminder == 0 ? 'Due today' : "Due in $daysUntilReminder days")
                        ];
                    }
                }
            }
        }
        return response()->json(['upcomingPayables' => $upcomingPayables], 200);
    }
    public function overdue()
    {
        $user = Auth::user();
        $payables = $user->payables;
        $currentDate = Carbon::now('UTC');
        $overDuePayables = [];
        foreach ($payables as $payable) {
            // Check if the receivable's payment term matches the due date
            if ($payable->status != "fullypaid") {
                if ($payable->payment_term === "equaltodueDate") {
                    $newDueDate = Carbon::parse($payable->dueDate, 'UTC');
                    if ($newDueDate->isBefore($currentDate)) {
                        $daysRemaining = $newDueDate->diffInDays($currentDate);
                        $overDuePayables[] = [
                            'id' => $payable->id,
                            'payableCreated' => $payable->date,
                            'supplier' => $payable->supplier->fullname,
                            'remaining' => $payable->remaining,
                            'status' => $payable->status,
                            'overdue' => $daysRemaining == 0 ? "Due yesterday" : "Over due" . $daysRemaining . "days ago"
                        ];
                    }
                } else {
                    $newDueDate = Carbon::parse($payable->dueDate, 'UTC');
                    $daysRemaining = $newDueDate->diffInDays($currentDate);
                    if ($newDueDate->isBefore($currentDate)) {
                        $overDuePayables[] = [
                            'id' => $payable->id,
                            'payableCreated' => $payable->date,
                            'supplier' => $payable->supplier->fullname,
                            'remaining' => $payable->remaining,
                            'status' => $payable->status,
                            'overdue' => $daysRemaining == 0 ? "Due yesterday" : "Over due" . $daysRemaining . "days ago"
                        ];
                    }
                }
            }
        }
        return response()->json(['overDuePayables' => $overDuePayables], 200);
    }
}
