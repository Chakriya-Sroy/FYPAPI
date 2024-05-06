<?php

namespace App\Http\Controllers\Merchance;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePayablePayment;
use App\Models\Payable;
use App\Models\PayablePayment;
use App\Models\PayableTransaction;
use App\Traits\HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayablePaymentController extends Controller
{
    //
    use HttpResponse;
    public function store(StorePayablePayment $request){
        $payable =Payable::find($request->payable_id);
        $user=Auth::user();
        if(!$payable){
            return $this->error('',"There no available resource");
        }
        $request->validated($request->all());
     
        if($payable->supplier->user_id !== $user->id){
            return $this->error('',"You not authorize to make payment under this resource");
        }
        if($request->amount > $payable->remaining){
            return $this->error('',"The amount enter exceed the remaining");
        }
        if($request->amount ==0){
            return $this->error('',"The aount enter must be greather than 0");
        }
        // update payment table
        $payment=PayablePayment::create([
            'amount'=>$request->amount,
            'payable_id'=>$payable->id,
            'date'=>now(),
            'remark'=>$request->remark,
            'attachment'=>$request->attachment
       ]);
       $transaction =PayableTransaction::create([
        'transaction_type'=>"payment",
        'amount'=>$payment->amount,
        'payment_id'=>$payment->id,
        'supplier_id'=>$payment->payable->supplier_id,
        'transaction_date'=>$payment->date,
        ]);
       // update payable table
       $requestAmount = (double) $request->amount;
       $newRemaining = max(0, $payable->remaining - $requestAmount);
       $newStatus = $newRemaining > 0 ? "partiallypaid" : "fullypaid";
       $payable->update([
        "remaining"=>$newRemaining,
        "status"=>$newStatus,
        "updated_at"=>now(),
       ]);
       return $this->success($payment,"The payment update successfully");

    }
}
