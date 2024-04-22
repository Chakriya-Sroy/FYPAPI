<?php

namespace App\Http\Controllers\Merchance;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\HttpResponse;
use App\Http\Resources\CustomerResource;
class CustomerController extends Controller
{
    use HttpResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CustomerResource::collection(Customer::where('user_id',Auth::user()->id)->get());
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
        $customer = Customer::create([
            'fullname' => $request->fullname,
            'gender' => $request->gender,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'remark' => $request->remark,
            'user_id' => Auth::user()->id
        ]);
        return $this->success(new CustomerResource($customer),"New Customer Create Successfully",201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {  
        if(Auth::user()->id !== $customer->user_id){
            return $this->error('', 'You are not authorized to view this resource', 401);
        }
        return new CustomerResource($customer);
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
    public function update(Request $request, Customer $customer)
    {
        if(Auth::user()->id !== $customer->user_id){
            return $this->error('', 'You are not authorized to view this resource', 401);
        }
        $customer->update($request->all());
        return $this->success(
            new CustomerResource($customer),'Customer Update Succesfully',201
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        if(Auth::user()->id !== $customer->user_id){
            return $this->error('', 'You are not authorized to view this resource', 401);
        }
        $customer->delete();
        return $this->success('',"Customer Delete Successfully");
    }

}
