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
        $payables = $user->payables->sortByDesc('created_at');
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
        if($request['attachment'] !=null){
        //   $path= $request['attachment']->storeAs('public',$request['attachment']->getClientOriginalName());
          $request['attachment']->storeAs('quiz4',$request['attachment']->getClientOriginalName(),'spaces');
       
        }
        $payable = Payable::create([
            'supplier_id' => $request->supplier_id,
            'amount' => $request->amount,
            'remaining' => $request->amount,
            'payment_term' => $request->payment_term,
            'status' => "outstanding",
            'date' => now(),
            'dueDate' => $request->dueDate,
            'attachment' => $request->attachment =='' ? '' : "https://testfyp1.sgp1.cdn.digitaloceanspaces.com/quiz4/{$request['attachment']->getClientOriginalName()}",
            'remark' => $request->remark,
        ]);
        // For Storing image
       
        $transaction = PayableTransaction::create([
            'transaction_type' => "payable",
            'amount' => $payable->amount,
            'payable_id' => $payable->id,
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
    public function update(Request $request, string $id)
    {
        //
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
                            'supplier' => $payable->supplier->fullname,
                            'remaining' => $payable->remaining,
                            'status' => $payable->status,
                            'upcoming' => "Due in $daysUntilReminder days"
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
