<?php

namespace App\Http\Controllers\Merchance;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReceivableRequest;
use App\Models\Customer;
use App\Models\Receivable;
use App\Traits\HttpResponse;
use Illuminate\Http\Request;
use App\Http\Resources\ReceivableResource;
use Illuminate\Support\Facades\Auth;

class ReceivableController extends Controller
{
    use HttpResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Customer $customer)
    {  
        if(Auth::user()->id !== $customer->user_id){
            return $this->error('','You not authorize to access this resource');
        }
        return ReceivableResource::collection(Receivable::where("customer_id",$customer->id)->get());
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
    public function store(StoreReceivableRequest $request,Customer $customer)
    { 
       //validate all request field 
       $request->validated($request->all());
       $receivable = Receivable::create([
        'customer_id'=>$customer->id,
        'amount' => $request->amount,
        'remaining' => $request->amount,
        'payment_term' => $request->payment_term,
        'status' => $request->status,
        'date' => $request->date,
        'dueDate' => $request->dueDate,
        'attachment' => $request->attachment,
        'remark' => $request->remark,
      ]);
      return $this->success(new ReceivableResource($receivable),'Receivable Create Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Receivable $receivable)
    {
        //
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
    public function update(Request $request, Receivable $receivable)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer,Receivable $receivable)
    {
        // //check if the user is authorize
        if(Auth::user()->id!== $customer->user_id){
            return $this->error('','You are not authorized to delete the resource');
        }
        $receivable->delete();
        return response()->json(null,201);
    }
}
