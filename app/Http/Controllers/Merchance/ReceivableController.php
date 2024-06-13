<?php

namespace App\Http\Controllers\Merchance;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReceivableRequest;
use App\Models\Customer;
use App\Models\Receivable;
use App\Traits\HttpResponse;
use Illuminate\Http\Request;
use App\Http\Resources\ReceivableResource;
use App\Http\Resources\UserResource;
use App\Models\CustomerAndCollector;
use App\Models\ReceivableTransaction;
use App\Models\User;
use App\Traits\HasCollectorRole;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Auth;

use function PHPUnit\Framework\isEmpty;

class ReceivableController extends Controller
{
    use HttpResponse, HasCollectorRole;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {   // Step 1: Get the current user's ID
        $user = Auth::user();
        $receivables = $user->receivables()->where('isArchive', false)->orderByDesc('created_at')->get();
        return $this->success(ReceivableResource::collection($receivables));
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
    public function store(StoreReceivableRequest $request)
    {
        $request->validated($request->all());
        $customer = Customer::find($request->customer_id);
        if (!$customer) {
            return $this->error('', "Customer didn't exist");
        }
        if ($customer->user_id !== Auth::user()->id) {
            return $this->error('', "You don't authorized to create any resource under this customer", 403);
        }
        $path = null;     
        if($request->file('attachment') !=null){
         $request->file('attachment') ->storeAs('public',$request['attachment']->getClientOriginalName());
         $path= $request->file('attachment')->storeAs('',$request['attachment']->getClientOriginalName(),'spaces');
        }
        $receivable = Receivable::create([
            'customer_id' => $request->customer_id,
            'amount' => $request->amount,
            'remaining' => $request->amount,
            'payment_term' => $request->payment_term,
            'status' => "oustanding",
            'date' => now(),
            'dueDate' => $request->dueDate,
            'attachment'=>$request->attachment == '' ? '' :"https://testfyp1.sgp1.cdn.digitaloceanspaces.com/$path",
            'remark' => $request->remark,
            'isArchive'=>false
        ]);
        $transactions = ReceivableTransaction::create([
            'receivable_id' => $receivable->id,
            'receivableCreated'=>$receivable->date,
            'amount' => $receivable->amount,
            'transaction_date' => $receivable->date,
            'customer_id' => $receivable->customer_id,
            'transaction_type' => 'receivable'
        ]);
        return $this->success(new ReceivableResource($receivable), 'New Receivable Create Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(String $id)
    {
        $receivable = Receivable::find($id);
        $user=Auth::user();
        //check if the resource is empty or not
        if (!$receivable) {
            return $this->error('', 'There no resource available');
        }
        // check if the user have authorize to view the resource or not
        if ($user->id == $receivable->customer->user_id) {
            return new ReceivableResource($receivable);
        }
        $isCollectorCustomer = CustomerAndCollector::where('customer_id', $receivable->customer->id)
        ->where('collector_id', $user->id)
        ->exists();
        if ( $isCollectorCustomer) {
           return  new ReceivableResource($receivable);
        }
        return $this->error('', 'You are not authorized to view the resource');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Receivable $receivable)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(string $id)
    {
        //
        $receivable = Receivable::find($id);
        if ($receivable) {
            $receivable->update(['isArchive' => true]);
            return response()->json(['message' => 'Receivable added to archive successfully']);
        } else {
            return response()->json(['message' => 'Receivable not found'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String  $id)
    {

        $receivable = Receivable::find($id);
        //check if the resource is empty or not
        if (!$receivable) {
            return $this->error('', 'There no resource available');
        }
        //check if the user is authorize
        if (Auth::user()->id !== $receivable->customer->user_id) {
            return $this->error('', 'You are not authorized to delete the resource');
        }
        $receivable->delete();
        return response()->json(null, 201);
    }


    public function upcoming()
    {
        $user = Auth::user();
        $receivables = $user->receivables;
        $currentDate = Carbon::now('UTC');
        $upcomingReceivables = [];
        foreach ($receivables as $receivable) {
            // Check if the receivable's payment term matches the due date
            if ($receivable->status != "fullypaid") {
                if ($receivable->payment_term === "equaltodueDate") {
                    $newDueDate = Carbon::parse($receivable->dueDate, 'UTC');
                    if ($newDueDate->isAfter($currentDate)) {
                        $daysRemaining = $newDueDate->diffInDays($currentDate);
                        if ($daysRemaining === 0 || $daysRemaining === 1) {
                            $upcomingReceivables[] = [
                                'id' => $receivable->id,
                                'receivableCreated'=>$receivable->date,
                                'customer' => $receivable->customer->fullname,
                                'remaining' => $receivable->remaining,
                                'status' => $receivable->status,
                                'upcoming' => $daysRemaining == 0 ? "Due Today" : "Due Tomorrow"
                            ];
                        }
                    }
                } else {
                    //Note just change the receiver today haven't done for payable yet wait until the receivable is correct
                    // Calculate the next reminder date
                    $reminderInterval = $receivable->payment_term; // 7 or 15 days
                    $nextReminderDate = $receivable->created_at->copy()->addDays($reminderInterval);

                    // Ensure the next reminder date is before the due date
                    if ($nextReminderDate->isBefore($receivable->dueDate)) {
                        // Calculate the days until the next reminder date
                        $daysUntilReminder = $currentDate->diffInDays($nextReminderDate, false);

                        // Ensure daysUntilReminder is positive
                        if ($daysUntilReminder > 0) {
                            // Prepare the upcoming receivables data
                            
                            $upcomingReceivables[] = [
                                'id' => $receivable->id,
                                'receivableCreated'=>$receivable->date,
                                'customer' => $receivable->customer->fullname,
                                'remaining' => $receivable->remaining,
                                'status' => $receivable->status,
                                'upcoming' =>  $daysUntilReminder == 1 ? 'Due tomorrow' : ($daysUntilReminder == 0 ? 'Due today' : "Due in $daysUntilReminder days")
                            ];
                        }
                    }
                }
            }
        }
        return response()->json(['upcomingReceivables' => $upcomingReceivables], 200);
    }
    public function overdue()
    {
        $user = Auth::user();
        $receivables = $user->receivables;
        $currentDate = Carbon::now('UTC');
        $overDueReceivables = [];
        foreach ($receivables as $receivable) {
            // Check if the receivable's payment term matches the due date
            if ($receivable->status != "fullypaid") {
                if ($receivable->payment_term === "equaltodueDate") {
                    // Calculate days remaining until the due date
                    $newDueDate = Carbon::parse($receivable->dueDate, 'UTC');
                    if ($newDueDate->isBefore($currentDate)) {
                        $daysRemaining = $newDueDate->diffInDays($currentDate);
                        $overDueReceivables[] = [
                            'id' => $receivable->id,
                            'receivableCreated'=>$receivable->date,
                            'customer' => $receivable->customer->fullname,
                            'remaining' => $receivable->remaining,
                            'status' => $receivable->status,
                            'overDue' => "OverDue $daysRemaining days"
                        ];
                    }
                } else {
                    $newDueDate = Carbon::parse($receivable->dueDate, 'UTC');
                    $daysRemaining = $newDueDate->diffInDays($currentDate);
                    if ($newDueDate->isBefore($currentDate)) {
                        $overDueReceivables[] = [
                            'id' => $receivable->id,
                            'receivableCreated'=>$receivable->date,
                            'customer' => $receivable->customer->fullname,
                            'remaining' => $receivable->remaining,
                            'status' => $receivable->status,
                            'overDue' => "OverDue $daysRemaining days"
                        ];
                    }
                }
            }
        }
        return response()->json(['overDueReceivables' => $overDueReceivables], 200);
    }
}
