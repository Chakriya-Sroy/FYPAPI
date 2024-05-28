<?php

namespace App\Http\Controllers\Merchance;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCollectorCustomer;
use App\Http\Resources\CustomerResource;
use App\Models\Collector;
use App\Models\Customer;
use App\Models\CustomerAndCollector;
use App\Models\User;
use App\Traits\HttpResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CollecterController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    use HttpResponse;
    public function viewAssignCustomer()
    {
        $userId = Auth::user()->id; // Assuming you want to get the current logged-in user's ID
        $customers = Customer::join('collector_customer', 'customers.id', '=', 'collector_customer.customer_id')
        ->where('collector_customer.collector_id', $userId) // Changed from 'cc.collector_id' to 'collector_customer.collector_id'
        ->select('customers.*')
        ->get();

        return response()->json(CustomerResource::collection($customers));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function viewtotalReceivableofAssignCustomer()
    {
        $user = Auth::user();
        $userId = $user->id;
        $results = DB::table('receivables as r')
            ->join('collector_customer as cc', 'r.customer_id', '=', 'cc.customer_id')
            ->where('cc.collector_id', $userId)
            ->select(DB::raw('SUM(r.amount) as total_amount, SUM(r.remaining) as total_remaining'))
            ->first();

        $totalAmount = $results->total_amount == null ? 0: $results->total_amount ;
        $totalRemaining = $results->total_remaining ==null ?0 : $results->total_remaining;
        $totalPaid=$totalAmount-$totalRemaining;
        return response()->json([
            'total_amount' => $totalAmount,
            'total_paid'=>$totalPaid,
            'total_remaining' => $totalRemaining
        ]);
    }
    public function getUpcomingAssignReceivableofAssignCustomer(){
        $user = Auth::user();
        $userId = $user->id;
        $receivables = DB::table('receivables as r')
        ->join('collector_customer as cc', 'r.customer_id', '=', 'cc.customer_id')
        ->where('cc.collector_id', $userId)
        ->select('r.*')
        ->get(); 
        $currentDate = Carbon::now('UTC');
        $upcomingReceivables = [];
        foreach ($receivables as $receivable) {
            // Check if the receivable's payment term matches the due date
           $customer= DB::table('customers')->where('id',$receivable->customer_id)->select('fullname')->first();
            if ($receivable->status != "fullypaid") {
                if ($receivable->payment_term === "equaltodueDate") {
                    $newDueDate = Carbon::parse($receivable->dueDate, 'UTC');
                    if ($newDueDate->isAfter($currentDate)) {
                        $daysRemaining = $newDueDate->diffInDays($currentDate);
                        if ($daysRemaining === 0 || $daysRemaining === 1) {
                            $upcomingReceivables[] = [
                                'id' => $receivable->id,
                                'customer' => $customer->fullname,
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
                                'customer' => $customer->fullname,
                                'remaining' => $receivable->remaining,
                                'status' => $receivable->status,
                                'upcoming' => "Due in $daysUntilReminder days"
                            ];
                        }
                    }
                }
            }
        }
        return response()->json(['upcomingReceivables' => $upcomingReceivables], 200);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }
    public function assign(StoreCollectorCustomer $request)
    {
        $request->validated($request->all());
        // Step 0 : check if the user have collector or not
        if (!$this->checkUserHasCollector()) {
            return $this->error('', "You don't have any collector");
        }
        // check if the collector provided match with the record or not
        if (!$this->isYourCollector($request)) {
            return $this->error('', "The collector's id provided didn't match with your record");
        }
        // Step 1 : Get the collector
        $collector = User::findorfail(Auth::user()->collector->collector_id);
        // Step 2 : Get the customer 
        $customer = Customer::find($request->customer_id);
        if (!$customer) {
            return $this->error('', 'There no customer with that Id exist');
        }
        // Step 3 : check if the customer belong to current user or not
        if (Auth::user()->id !== $customer->user_id) {
            return $this->error('', "You not aurthorized to assign customer to any collector");
        }
        if ($this->CustomerAlreadyAssignToCollector($request)) {
            return $this->error('', "Customer already assigned to collector before");
        }
        // Step 4 Assign customer to collector
        CustomerAndCollector::create(['collector_id' => $collector->id, 'customer_id' => $customer->id]);
        return $this->success($customer, 'Customer has been assign to collector successfully');
    }

    public function unassign(StoreCollectorCustomer $request)
    {
        $request->validated($request->all());
        // Step 0 : check if the user have collector or not
        if (!$this->checkUserHasCollector()) {
            return $this->error('', "You don't have any collector");
        }

        if (!$this->isYourCollector($request)) {
            return $this->error('', "The collector's id provided didn't match with your record");
        }

        // Step 1 : Get the collector that associate with the user
        $collector = User::find(Auth::user()->collector->collector_id);
        // Step 2 : Get the customer 
        $customer = Customer::find($request->customer_id);
        if (!$customer) {
            return $this->error('', 'There no customer with that Id exist');
        }
        // Step 3 : check if the customer belong to current user or not
        if (Auth::user()->id !== $customer->user_id) {
            return $this->error('', "You not aurthorized to access the resource");
        }
        // check if the user  assign that customer to collector before
        if (!$this->CustomerAlreadyAssignToCollector($request)) {
            return $this->error('', "Customer never been assign to collector before");
        }
        // Step 4 Unassign customer to collector
        CustomerAndCollector::where('collector_id', $collector->id)
            ->where('customer_id', $customer->id)
            ->delete();
        return $this->success($customer, 'Customer has been unassigned from the collector successfully');
    }
    private function checkUserHasCollector()
    {
        $user = Auth::user();
        $hasCollector = Collector::where('user_id', $user->id)->get();
        return $hasCollector->count() > 0;
    }
    private function isYourCollector(Request $request)
    {
        $collector = Collector::where('collector_id', $request->collector_id)->where('user_id', Auth::user()->id)->get();
        if ($collector->count() == 0) {
            return false;
        }
        return true;
    }
    private function CustomerAlreadyAssignToCollector(Request $request)
    {
        $customer = CustomerAndCollector::where('customer_id', $request->customer_id)
            ->where('collector_id', $request->collector_id)
            ->get();
        if ($customer->count() == 0) {
            return false;
        }
        return true;
    }
}
