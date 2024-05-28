<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCollectorCustomer;
use App\Http\Requests\StoreCollectorInvitation;
use App\Http\Resources\ReceiverInvitationResource;
use App\Http\Resources\SenderInvitationResource;
use App\Http\Resources\UserResource;
use App\Models\Collector;
use App\Models\Customer;
use App\Models\CustomerAndCollector;
use App\Models\Invitation;
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
    public function showReceivedInvitation()
    {
        // Invitation that hase been received
        $user = Auth::user();
        $showSenderInfo = [];
        $receivedInvitations = $user->receivedInvitations->sortByDesc('created_at');
        foreach ($receivedInvitations as $invitation) {
           if($invitation->status =='pending'){
            $showSenderInfo[] = [
                'sender' => User::find($invitation->sender_id),
                'status' => $invitation->status,
                'date' => $invitation->created_at
            ];
           }
        }
        return response()->json($showSenderInfo, 200);
    }
    public function showRequestInvitation()
    {
        // Invitation that has been send
        $user = Auth::user();
        $sentInvitations = $user->sentInvitations->sortByDesc('created_at');

        // Prepare the response data
        $showUserRequestofInvitation = [];
        foreach ($sentInvitations as $invitation) {
            $showUserRequestofInvitation[] = [
                'receiver' => User::find($invitation->receiver_id),
                'status' => $invitation->status,
                'date' => $invitation->created_at
            ];
        }
        return response()->json($showUserRequestofInvitation, 200);
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
        // //Step 0 :First validate all field
        // $request->validated($request->all());
        // // Step 1 :check if the current user have any collector or not
        // $user = Auth::user();
        // if ($this->checkUserHasCollector()) {
        //     return $this->error('', 'User already have collector');
        // }
        // // Step 2 : Check if the target user have been accept inviation befor or not
        // if ($this->hasAcceptedCollectorInvitation($request->user_id)) {
        //     return $this->error('', 'The target user already accept as collector by other user');
        // }
        // if ($request->user_id == $user->id) {
        //     return $this->error('', "You can't become your own collector");
        // }
        // $inviteUser = User::findorfail($request->user_id);
        // $roleUpdate = false;
        // if ($request->status == "accepted") {
        //     $roleUpdate = true;
        //     // Update UserandRole Table
        //     UserandRole::create(['user_id' => $inviteUser->id, 'role_id' => 2]);
        //     // Update CollectorandUser
        //     Collector::create(['user_id' => $user->id, 'collector_id' => $inviteUser->id]);
        //     return response()->json(
        //         ['status' => $request->status, 'inviteBy' => $user, "acceptBy" => $inviteUser, "inviteUserrRoleUpdate" => $roleUpdate]
        //     );
        // }
        // return response()->json(
        //     [
        //         "Message" => "Invitation has been decline",
        //     ]
        // );
    }

    /**
     * Display the specified resource.
     */

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
            'collector_id' => 'required'
        ]);
        // Check if user have collecctor or not
        // check if the collector that request to being delete is really their collector
        // update collector table;
        $collector = Collector::find($request->collector_id);
        if (!$this->checkUserHasCollector()) {
            return $this->error('', "User don't have collector to remove");
        }
        if (!$collector) {
            return $this->error('', "Collector's id didn't exist");
        }
        if (!$this->isYourCollector($request)) {
            return $this->error('', "Collector's id didn't match with your record");
        }
        //Remove collector from Collector Table so user no longer have collector and it will be set to false
        Collector::where('collector_id', $request->collector_id)->delete();
        //Remove collector from Customer and Collector so user that used to be assign before no longer see
        CustomerAndCollector::where('collector_id', $request->collector_id)->delete();
        return $this->success("", "Collector have been remove");
    }
    private function checkUserHasCollector()
    {
        $user = Auth::user();
        $hasCollector = Collector::where('user_id', $user->id)->get();
        return $hasCollector->count() > 0;
    }
    private function isYourCollector(Request $request)
    {
        $collector = Collector::where('collector_id', $request->collector_id)->where('user_id', Auth::user()->id)->get();
        if ($collector->count() == 0) {
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
    private function CustomerAlreadyAssignToCollector(Request $request)
    {
        $customer = CustomerAndCollector::where('customer_id', $request->customer_id)
            ->where('collector_id', $request->collector_id)
            ->get();
        if ($customer->count() == 0) {
            return false;
        }
        return true;
    }

    public function cancelInvitation(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();

        // Find the invitation by sender_id and receiver_id
        $invitation = Invitation::where('sender_id', $user->id)
            ->where('receiver_id', $request->receiver_id)
            ->where('status', 'pending') // Ensure only pending invitations can be canceled
            ->first();

        if (!$invitation) {
            return response()->json(['message' => "No pending invitation found to cancel"], 400);
        }

        // Delete or update the invitation status to 'canceled'
        $invitation->status = 'canceled';
        $invitation->save();

        return response()->json(['message' => 'Invitation has been canceled successfully'], 200);
    }

    public function sendInvitation(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();
        if ($this->checkUserHasCollector()) {
            return response()->json('User already have collector', 400);
        }
        if ($request->receiver_id == $user->id) {
            return response()->json("You can't become your own collector", 400);
        }
        // Check if user made the same request multiple times
        $existingInvitation = Invitation::where('sender_id', $user->id)
            ->where('receiver_id', $request->receiver_id)
            ->where('status', 'pending')
            ->first();
        if ($existingInvitation) {
            return response()->json(['message' => 'You have already sent a request to this user'], 400);
        }
        // check if user make the same request multiple time
        $invitation = Invitation::create([
            'sender_id' => $user->id,
            'receiver_id' => $request->receiver_id,
            'status' => 'pending',
        ]);

        return response()->json(['message' => "Your request has been send successfully", 'invitation' => $invitation], 200);
    }
    public function respondInvitation(Request $request)
    {
        // Validate the request to ensure 'status' is either 'accepted' or 'declined'
        $request->validate([
            'sender_id' => 'required|exists:invitations,sender_id',
            'status' => 'required|in:accepted,declined',
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Find the invitation by its ID
        $invitation = Invitation::where('receiver_id', $user->id)
            ->where('sender_id', $request->input('sender_id'))
            ->firstOrFail();


        if (!$invitation) {
            return response()->json(['message' => "You have no request"], 400);
        }
        // If the status is 'accepted'
        if ($request->status == 'accepted') {
            // Check if the user has already accepted another invitation
            if ($this->hasAcceptedCollectorInvitation($user->id)) {
                return response()->json(['message' => 'You have already accepted another invitation'], 400);
            }

            // Mark the invitation as accepted
            $invitation->status = 'accepted';
            $invitation->save();

            // Update UserandRole Table to assign role to receiver
            UserandRole::create(['user_id' => $user->id, 'role_id' => 2]);

            // Update CollectorandUser table to establish the collector relationship
            Collector::create(['user_id' => $invitation->sender_id, 'collector_id' => $user->id]);
        } else {
            // If the status is 'declined', mark the invitation as declined
            $invitation->status = 'declined';
            $invitation->save();
        }

        // Return the updated invitation
        return response()->json(["message"=>"User $invitation->status request"],200);
    }
}
