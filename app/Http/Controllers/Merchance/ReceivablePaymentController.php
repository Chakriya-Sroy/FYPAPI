<?php

namespace App\Http\Controllers\Merchance;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReceivablePayment;
use App\Http\Resources\ReceivablePaymentResource;
use App\Models\Customer;
use App\Models\Receivable;
use App\Models\ReceivablePayment;
use App\Traits\HttpResponse;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceivablePaymentController extends Controller
{
    use HttpResponse;
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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReceivablePayment $request,Receivable $receivable)
    {  
        $notification="";
        //validate the field
        $request->validated($request->all());
        //check if the amount that being paid is less greater than the remaining or not
        if($request->amount > $receivable->remaining){
            return $this->error('',"The amount can't be exceed the remaining",201);
        }
        elseif($request->amount ==0){
            return $this->error('',"The amount can't be zero",201);
        }
        $payment=ReceivablePayment::create([
              'amount'=>$request->amount,
              'user_id'=>Auth::user()->id,
              'receivable_id'=>$receivable->id,
              'date'=>now(),
              'remark'=>$request->remark,
              'attachment'=>$request->attachment
        ]);
        $requestAmount = (double) $request->amount;
        $newRemaining = max(0, $receivable->remaining - $requestAmount);
        $newStatus = $newRemaining > 0 ? "partiallypaid" : "fullypaid";
        $receivable->update([
            'remaining' => $newRemaining,
            'status'=> $newStatus,
            'updated_at'=>now()
        ]);
        $receivable->save();
        if(Auth::user()->id == $receivable->customer->user_id){
            $notification ="The payment update by owner";
        }else{
            $notification="The payment update by collector";
        }
        return $this->success([new ReceivablePaymentResource($payment),$notification],"The payment update successfully");

    }

    /**
     * Display the specified resource.
     */
    public function show(ReceivablePayment $receivablePayment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReceivablePayment $receivablePayment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReceivablePayment $receivablePayment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReceivablePayment $receivablePayment)
    {
        //
    }
}
