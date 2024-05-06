<?php

namespace App\Http\Controllers\Merchance;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReceivablePayment;
use App\Http\Resources\ReceivablePaymentResource;
use App\Models\Collector;
use App\Models\Customer;
use App\Models\CustomerAndCollector;
use App\Models\Receivable;
use App\Models\ReceivablePayment;
use App\Models\ReceivableTransaction;
use App\Traits\HttpResponse;
use Collator;
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
    public function store(StoreReceivablePayment $request)
    {  
        $notification="";
        // step 1 : check if the receivable exist or not
        $receivable=Receivable::find($request->receivable_id);
        $user=Auth::user();

        $isCollector=CustomerAndCollector::where('collector_id',$user->id)
                                        ->where('customer_id',$receivable->customer_id)
                                        ->get();
        $hasCollecctor = Collector::where('user_id',$user->id)->get();
        if(!$receivable){
            return $this->error('',"There no available resource");
        }
        $request->validated($request->all());
        if($isCollector->count()==0 && $hasCollecctor->count()==0){
            return $this->error('','You not authorize to make any payment under this resource');
        }

        //step 2 : check if the receivable is belong to current user or not or they have collector_id that associate with customer that own receivable
        
        //step 3 : check all validate of the field

       

        //step 4 : check if the amount that being input is exceed the remaining of receivable or not

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
        $transactions = ReceivableTransaction::create([
            'payable_id'=>$payment->id,
            'amount'=>$payment->amount,
            'transaction_date'=>$payment->date,
            'customer_id'=>$payment->receivable->customer_id,
            'transaction_type'=>'payable'
          ]);
        $requestAmount = (double) $request->amount;
        $newRemaining = max(0, $receivable->remaining - $requestAmount);
        $newStatus = $newRemaining > 0 ? "partiallypaid" : "fullypaid";

        // Step 5 update the receivable

        $receivable->update([
            'remaining' => $newRemaining,
            'status'=> $newStatus,
            'updated_at'=>now()
        ]);

        // final Step confirm who update the receivable
        if($user->id == $receivable->customer->user_id){
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
        // step 1 check if 
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
