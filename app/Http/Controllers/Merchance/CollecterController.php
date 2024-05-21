<?php

namespace App\Http\Controllers\Merchance;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCollectorCustomer;
use App\Models\Customer;
use App\Models\CustomerAndCollector;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollecterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // view customer under their controll
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
        return $this->success('', 'Customer has been assign to collector successfully');
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
        return $this->success('', 'Customer has been unassigned from the collector successfully');
    }

}
