<?php

namespace App\Http\Controllers\Merchance;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use App\Traits\HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    use HttpResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $user =Auth::user();
        $supplier =$user->suppliers->sortByDesc('created_at');
        return $this->success(SupplierResource::collection($supplier));
       
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
    public function store(StoreSupplierRequest $request)
    {

        $request->validated($request->all());
        $user=Auth::user();
        $subscription=$user->subscription;
        if(!$subscription){
            // check if the customer exceed the 5
            if($user->suppliers->count() >= 5){
                return $this->error("","Opp, You already exceed the allow suppliers,Please subscription to Premium plan");
            }
            $supplier=Supplier::create(
                [
                    'fullname' => $request->fullname,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'remark' => $request->remark,
                    "user_id"=>Auth::user()->id,
                ]
            );
            return $this->success(new SupplierResource($supplier),'New Supplier create succesfully');
        }
        // else{
        //     if(!$subscription->active && now()->isAfter($subscription->end)){
        //         return $this->error("","Please renew the plan to enjoy unlimited");
        //     }
        // }
        if (!$subscription->active && now()->isAfter($subscription->end)) {
            return $this->error("", "Please renew the plan to enjoy unlimited suppliers.");
        }

        if($user->suppliers->count()>=15){
            return $this->error("", "Oops, you have already exceeded the allowed number of suppliers.");
        }  
        $supplier=Supplier::create(
            [
                'fullname' => $request->fullname,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'remark' => $request->remark,
                "user_id"=>Auth::user()->id,
            ]
        );
        return $this->success(new SupplierResource($supplier),'New Supplier create succesfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(String $id)
    {
        $supplier = Supplier::find($id);

        if (!$supplier) {
            return $this->error("", "The requested resource does not exist.");
        }
    
        // Step 1: Check if the user is authorized to view the resource
        if (Auth::id() == $supplier->user_id) {
            return new SupplierResource($supplier);
        }
    
        // Step 2: Return an error message indicating unauthorized access
        return $this->error('', "You are not authorized to view this resource.");
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
    public function update(StoreSupplierRequest $request, String $id)
    {
        // check if the resource is available or not
        $supplier =Supplier::find($id);
        if(!$supplier){
            return $this->error('',"The resource does not exist");
        }
        $request->validated($request->all());
        if (Auth::user()->id !== $supplier->user_id) {
            return $this->error('', 'You are not authorized to view this resource', 401);
        }
        $supplier->update($request->all());
        return $this->success(
            new SupplierResource($supplier),
            'Supplier Update Succesfully',
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $supplier =Supplier::find($id);
        if(!$supplier){
            return $this->error('','The resource does not exist');
        }
        if($supplier->user_id !== Auth::user()->id){
            return $this->error('',"You not authorize to delete the resource");
        }
        $supplier->delete();
        return $this->success("","The supplier delete successfully");
    }

    public function transaction(String $id){
        
        $supplier =Supplier::find($id);
        if(!$supplier){
            return $this->error('','The resource does not exist');
        }
        if($supplier->user_id !== Auth::user()->id){
            return $this->error('',"You not authorize to view the resource");
        }
        $transaction =$supplier->transactions;
        return $this->success($transaction);
    }
}
