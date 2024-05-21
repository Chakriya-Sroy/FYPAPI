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
    use HttpResponse,HasCollectorRole;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user=Auth::user();
        $assign_customer=[];
        $assignCustomer =CustomerAndCollector::where('collector_id',$user->id)->get();
        if($assignCustomer->count()==1){
           $id=$assignCustomer[0]->customer_id;
           $assign_customer=Customer::find($id);
        }
        else if($assignCustomer->count()>1){
            foreach($assignCustomer as $assign){
                $assign_customer[]=Customer::find($assign->customer_id);
            }
        }
        
        return $this->success(
            [
                "Customer" => CustomerResource::collection($user->customers->sortByDesc('created_at')),
                "Assign Customer" =>CustomerResource::collection( $assign_customer)
            ],
            '',
        );
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
    public function store(StoreCustomerRequest $request)
    {
        $request->validated($request->all());
        $user =Auth::user();
        $subscription=$user->subscription;
        if(!$subscription){
            // check if the customer exceed the 5
            if($user->customers->count() > 5){
                return $this->error("","Opp, You already exceed the allow customers,Please subscription to Premium plan");
            }
        }
        if($subscription && $subscription->active ==false){
            return $this->error("","Please renew the plan to enjoy unlimited");
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
        $collectorInfo = $this->hasCollectorRole();
        $collectorId = $collectorInfo["collectorId"];
        $isCollector = $collectorInfo["isCollector"];
        //Step 1: check if the user has collector authroization to view the resource or not
        if(Auth::user()->id == $customer->user_id){
            return new CustomerResource($customer);
        }
        //Step 2 : Check if the current user has authorized to view the resource or not
        if ($isCollector) {
            //  Check if the user that being request to view and user has relationship with each other or not
            $target_customer = CustomerAndCollector::where('customer_id', $customer->id)
            ->where('collector_id', $collectorId)
            ->get();
            if($target_customer->count()>0){
                return new CustomerResource($customer);
            }
            return $this->error('','You are not authorized to view this resource');
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
        $customer=Customer::find($id);
        if(!$customer){
            return $this->error('','There no available resource ');
        }
        if (Auth::user()->id !== $customer->user_id) {
            return $this->error('', 'You are not authorized to view this resource', 401);
        }
        $transaction =$customer->transactions;
        return $this->success($transaction);
    }
}
