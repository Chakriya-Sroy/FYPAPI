<?php

namespace App\Http\Controllers\Merchance;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePayableRequest;
use App\Http\Resources\PayableResource;
use App\Models\Payable;
use App\Models\PayableTransaction;
use App\Models\Supplier;
use App\Traits\HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayableController extends Controller
{
    use HttpResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $user =Auth::user();
        $payables=$user->payables;
        if($payables->count()==0){
            return "There no resource available yet";
        }
        return $payables;
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
    public function store(StorePayableRequest $request)
    {
        $request->validated($request->all());
        $supplier =Supplier::find($request->supplier_id);
        if(!$supplier){
            return $this->error('',"Supplier didn't exist");
        }
        if($supplier->user_id!==Auth::user()->id){
            return $this->error('', "You don't authorized to create any resource under this supplier", 403);
        }
        $payable = Payable::create([
            'supplier_id'=>$request->supplier_id,
            'title'=>$request->title,
            'amount' => $request->amount,
            'remaining' => $request->amount,
            'payment_term' => $request->payment_term,
            'status' => "outstanding",
            'date' => now(),
            'dueDate' => $request->dueDate,
            'attachment' => $request->attachment,
            'remark' => $request->remark,
        ]);
        $transaction =PayableTransaction::create([
            'transaction_type'=>"payable",
            'amount'=>$payable->amount,
            'payable_id'=>$payable->id,
            'supplier_id'=>$payable->supplier_id,
            'transaction_date'=>$payable->date,
        ]);
        return $this->success(new PayableResource($payable),'The payable create successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $supplier =Supplier::find($id);
        if($supplier){
            return $this->error('','There no resource exist');
        }
        if($supplier->user_id !== Auth::user()->id){
            return $this->error('','You not authorized to view this resource');
        }
        $payable=Payable::find($id);
        return new PayableResource($payable);
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
        $supplier =Supplier::find($id);
        if($supplier){
            return $this->error('','There no resource exist');
        }
        if($supplier->user_id !== Auth::user()->id){
            return $this->error('','You not authorized to delete this resource');
        }
        $payable=Payable::find($id)->delete();
        return response()->json(null,201);
    }
}
