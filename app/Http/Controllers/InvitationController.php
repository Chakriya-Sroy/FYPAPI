<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCollectorCustomer;
use App\Http\Requests\StoreCollectorInvitation;
use App\Http\Resources\UserResource;
use App\Models\Collector;
use App\Models\Customer;
use App\Models\CustomerAndCollector;
use App\Traits\HttpResponse;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserandRole;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
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
    public function store(StoreCollectorInvitation $request)
    {
        //Step 0 :First validate all field
        $request->validated($request->all());
        // Step 1 :check if the current user have any collector or not
        $user = Auth::user();
        if ($this->checkUserHasCollector()) {
            return $this->error('', 'User already have collector');
        }
        // Step 2 : Check if the target user have been accept inviation befor or not
        if ($this->hasAcceptedCollectorInvitation($request->user_id)) {
            return $this->error('', 'The target user already accept as collector by other user');
        }
        if($request->user_id==$user->id){
            return $this->error('',"You can't become your own collector");
        }
        $inviteUser = User::findorfail($request->user_id);
        $roleUpdate = false;
        if ($request->status == "accepted") {
            $roleUpdate = true;
            // Update UserandRole Table
            UserandRole::create(['user_id' => $inviteUser->id, 'role_id' => 2]);
            // Update CollectorandUser
            Collector::create(['user_id' => $user->id, 'collector_id' => $inviteUser->id]);
            return response()->json(
                ['status' => $request->status, 'inviteBy' => $user, "acceptBy" => $inviteUser, "inviteUserrRoleUpdate" => $roleUpdate]
            );
        }
        return response()->json(
            [
                "Message" => "Invitation has been decline",
            ]
        );
    }

    /**
     * Display the specified resource.
     */
    public function assign(StoreCollectorCustomer $request)
    {
        $request->validated($request->all());
        // Step 0 : check if the user have collector or not
        if (!$this->checkUserHasCollector()) {
            return $this->error('', "You don't have any collector");
        }
        // check if the collector provided match with the record or not
        if(!$this->isYourCollector($request)){
            return $this->error('',"The collector's id provided didn't match with your record");
        }
        // Step 1 : Get the collector
        $collector = User::findorfail(Auth::user()->collector->collector_id);
        // Step 2 : Get the customer 
        $customer = Customer::find($request->customer_id);
        if(!$customer){
            return $this->error('','There no customer with that Id exist');
        }
        // Step 3 : check if the customer belong to current user or not
        if (Auth::user()->id !== $customer->user_id) {
            return $this->error('', "You not aurthorized to assign customer to any collector");
        }
        if($this->CustomerAlreadyAssignToCollector($request)){
            return $this->error('', "Customer already assigned to collector before");
        }
        // Step 4 Assign customer to collector
        CustomerAndCollector::create(['collector_id' => $collector->id, 'customer_id' => $customer->id]);
        return $this->success('', 'Customer has been assign to collector successfully');
    }

    public function unassign(StoreCollectorCustomer $request)
    {
        $request->validated($request->all());
        // Step 0 : check if the user have collector or not
        if (!$this->checkUserHasCollector()) {
            return $this->error('', "You don't have any collector");
        }
        
        if(!$this->isYourCollector($request)){
            return $this->error('',"The collector's id provided didn't match with your record");
        }

        // Step 1 : Get the collector that associate with the user
        $collector = User::find(Auth::user()->collector->collector_id);
        // Step 2 : Get the customer 
        $customer = Customer::find($request->customer_id);
        if(!$customer){
            return $this->error('','There no customer with that Id exist');
        }
        // Step 3 : check if the customer belong to current user or not
        if (Auth::user()->id !== $customer->user_id) {
            return $this->error('', "You not aurthorized to access the resource");
        }
        // check if the user  assign that customer to collector before
        if(!$this->CustomerAlreadyAssignToCollector($request)){
            return $this->error('', "Customer never been assign to collector before");
        }
        // Step 4 Unassign customer to collector
        CustomerAndCollector::where('collector_id', $collector->id)
            ->where('customer_id', $customer->id)
            ->delete();
        return $this->success('', 'Customer has been unassigned from the collector successfully');
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
    public function remove(Request $request)
    {
        $request->validate([
            'collector_id'=>'required'
        ]);
        // Check if user have collecctor or not
        // check if the collector that request to being delete is really their collector
        // update collector table;
        $collector =Collector::find($request->collector_id);
        if(!$this->checkUserHasCollector()){
            return $this->error('',"User don't have collector to remove");
        }
        if(!$collector){
            return $this->error('',"Collector's id didn't exist");
        }
        if(!$this->isYourCollector($request)){
            return $this->error('',"Collector's id didn't match with your record");
        }
        //Remove collector from Collector Table so user no longer have collector and it will be set to false
        Collector::where('collector_id',$request->collector_id)->delete();
        //Remove collector from Customer and Collector so user that used to be assign before no longer see
        CustomerAndCollector::where('collector_id',$request->collector_id)->delete();
        return $this->success("","Collector have been remove");
    }
    private function checkUserHasCollector()
    {
        $user = Auth::user();
        $hasCollector = Collector::where('user_id', $user->id)->get();
        return $hasCollector->count() > 0;
    }
    private function isYourCollector(Request $request){
        $collector = Collector::where('collector_id',$request->collector_id)->where('user_id',Auth::user()->id)->get();
        if($collector->count()==0){
            return false;
        }
        return true;
    }
    private function hasAcceptedCollectorInvitation($userId)
    {
        $inviteUser = User::findOrFail($userId);
        $alreadyAcceptSomeoneElse = Collector::where('collector_id', $inviteUser->id)->get();
        return $alreadyAcceptSomeoneElse->count() > 0;
    }
    private function CustomerAlreadyAssignToCollector(Request $request){
        $customer = CustomerAndCollector::where('customer_id',$request->customer_id)
                    ->where('collector_id',$request->collector_id)
                    ->get();
        if($customer->count()==0){
            return false;
        }
        return true;
    }

 
}
