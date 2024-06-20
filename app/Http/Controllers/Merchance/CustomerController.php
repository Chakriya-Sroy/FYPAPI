<?php

namespace App\Http\Controllers\Merchance;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\HttpResponse;
use App\Http\Resources\CustomerResource;
use App\Models\Collector;
use App\Models\CustomerAndCollector;
use App\Models\Role;
use App\Models\User;
use App\Models\UserandRole;
use App\Traits\HasCollectorRole;

class CustomerController extends Controller
{
    use HttpResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user=Auth::user();      
        return $this->success(CustomerResource::collection($user->customers->sortByDesc('created_at')));
    }

    /**_
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        $request->validated($request->all());
        $user =Auth::user();
        $subscription=$user->subscription;
        if (!$subscription) {
            // Check if the user exceeds the allowed number of customers
            if ($user->customers->count() >= 5) {  // Changed > to >= to properly limit to 5 customers
                return $this->error("", "Oops, you have already exceeded the allowed number of customers. Please subscribe to the Premium plan.");
            }
        }
        if (!$subscription->active && now()->isAfter($subscription->end)) {
            return $this->error("", "Please renew the plan to enjoy unlimited customers.");
        }

        if($user->customers->count()>=15){
            return $this->error("", "Oops, you have already exceeded the allowed number of customers.");
        }  
        $customer = Customer::create([
            'fullname' => $request->fullname,
            'gender' => $request->gender,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'remark' => $request->remark,
            'user_id' => Auth::user()->id
        ]);
        return $this->success(new CustomerResource($customer), "New Customer Create Successfully");
       
    }

    /**
     * Display the specified resource.
     */
    public function show(String $id)
    {
        $customer=Customer::find($id);
        if(!$customer){
            return $this->error('','There no available resource ');
        }
        // $collectorInfo = $this->hasCollectorRole();
        // $collectorId = $collectorInfo["collectorId"];
        // $isCollector = $collectorInfo["isCollector"];
        //Step 1: check if the user has collector authroization to view the resource or not
        if(Auth::user()->id == $customer->user_id){
            return new CustomerResource($customer);
        }
        //Step 2 : Check if the current user has authorized to view the resource or not
        $isCollector = CustomerAndCollector::where('customer_id', $customer->id)->where('collector_id',Auth::user()->id)->exists();
        if ($isCollector) {    
            return new CustomerResource($customer);
        }
        
        return $this->error('', 'You are not authorized to view this resource', 401);
       // return new CustomerResource($customer);
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
    public function update(StoreCustomerRequest $request, String $id)
    {
        $customer=Customer::find($id);
        $request->validated($request->all());
        if(!$customer){
            return $this->error('','There no available resource ');
        }
        if (Auth::user()->id !== $customer->user_id) {
            return $this->error('', 'You are not authorized to view this resource', 401);
        }
        $customer->update($request->all());
        return $this->success(
            new CustomerResource($customer),
            'Customer Update Succesfully',
            200
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String  $id)
    {
        $customer=Customer::find($id);
        if(!$customer){
            return $this->error('','There no available resource ');
        }
        if (Auth::user()->id !== $customer->user_id) {
            return $this->error('', 'You are not authorized to delete this resource', 401);
        }
        $customer->delete();
        return $this->success('', "Customer Delete Successfully");
    }

    public function transaction(String $id){
        $user =Auth::user();

        $customer=Customer::find($id);
        if(!$customer){
            return $this->error('','There no available resource ');
        }
        if ($user->id == $customer->user_id) {
            return $this->success($customer->transactions);
        }
        $isCollectorCustomer = CustomerAndCollector::where('customer_id', $customer->id)
        ->where('collector_id', $user->id)
        ->exists();
        if($isCollectorCustomer){
            return $this->success($customer->transactions);
        }
        
        return $this->error('', 'You are not authorized to view this resource', 401);
    }

    public function getAssignedCustomers() // just to find the list of customer that assign to collector
    {
        // The purpose find the customer that owner assign to collector
        $user = Auth::user();
        // Fetch assigned customers with a join
        $assignedCustomers = Customer::join('collector_customer', 'customers.id', '=', 'collector_customer.customer_id')
            ->where('customers.user_id', $user->id)
            ->select('customers.*')
            ->get();

        return $this->success($assignedCustomers);
    }
}
