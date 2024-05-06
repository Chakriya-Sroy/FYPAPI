<?php

namespace App\Http\Controllers\Merchance;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReceivableRequest;
use App\Models\Customer;
use App\Models\Receivable;
use App\Traits\HttpResponse;
use Illuminate\Http\Request;
use App\Http\Resources\ReceivableResource;
use App\Http\Resources\UserResource;
use App\Models\CustomerAndCollector;
use App\Models\ReceivableTransaction;
use App\Models\User;
use App\Traits\HasCollectorRole;
use Illuminate\Support\Facades\Auth;

use function PHPUnit\Framework\isEmpty;

class ReceivableController extends Controller
{
    use HttpResponse,HasCollectorRole;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {   // Step 1: Get the current user's ID
        $user =Auth::user();
        // Step 2: Get all customers under the current user & customers that were assign to user
        $customers =$user->customers;
        $assignCustomer=CustomerAndCollector::where('collector_id',Auth::user()->id)->get();
        if($customers->isEmpty()){
            return "There no resource avaialbel yet";
        }
        // Step 3: Initialize arrays to store receivables
        $receivables=$user->receivables;
        $assginReceivable=[];
        // Step 5: Iterate over each assign customer
        foreach($assignCustomer as $customer){
            $assginReceivable[]=ReceivableResource::collection(Receivable::where("customer_id",$customer->customer_id)->get());
        }
        return $this->success(
            [
                "receivables"=>$receivables,
                "assignReceivable"=>$assginReceivable
            ]
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
    public function store(StoreReceivableRequest $request)
    { 
       $request->validated($request->all());
       $customer =Customer::find($request->customer_id);
       if(!$customer){
        return $this->error('',"Customer didn't exist");
       }
       if($customer->user_id!==Auth::user()->id){
        return $this->error('', "You don't authorized to create any resource under this customer", 403);
       }
       $receivable = Receivable::create([
        'customer_id'=>$request->customer_id,
        'title'=>$request->title,
        'amount' => $request->amount,
        'remaining' => $request->amount,
        'payment_term' => $request->payment_term,
        'status' => "oustanding",
        'date' => now(),
        'dueDate' => $request->dueDate,
        'attachment' => $request->attachment,
        'remark' => $request->remark,
      ]);
      $transactions = ReceivableTransaction::create([
        'receivable_id'=>$receivable->id,
        'amount'=>$receivable->amount,
        'transaction_date'=>$receivable->date,
        'customer_id'=>$receivable->customer_id,
        'transaction_type'=>'receivable'
      ]);
      return $this->success(new ReceivableResource($receivable),'New Receivable Create Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(String $id)
    {
        $receivable=Receivable::find($id);
        //check if the resource is empty or not
        if(!$receivable){
            return $this->error('','There no resource available');
        }
        // check if the user have authorize to view the resource or not
        if(Auth::user()->id==$receivable->customer->user_id){
            return new ReceivableResource($receivable);
        }
        $collector=CustomerAndCollector::where('collector_id',Auth::user()->id)->where('customer_id',$receivable->customer->id)->get();
        if($collector->count()>0){
            return response()->json([
                'assign_receivable'=>new ReceivableResource($receivable)
            ]);
        }
        return $this->error('','You are not authorized to view the resource');
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
    public function destroy(String  $id)
    {
        
        $receivable=Receivable::find($id);
        //check if the resource is empty or not
        if(!$receivable){
            return $this->error('','There no resource available');
        }
        //check if the user is authorize
        if(Auth::user()->id!== $receivable->customer->user_id){
            return $this->error('','You are not authorized to delete the resource');
        }
        $receivable->delete();
        return response()->json(null,201);
    }
}
