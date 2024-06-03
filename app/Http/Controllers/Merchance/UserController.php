<?php

namespace App\Http\Controllers\Merchance;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Invitation;
use App\Models\Notifications;
use App\Models\User;
use App\Traits\HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function Laravel\Prompts\error;

class UserController extends Controller
{
    use HttpResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $user = Auth::user();
        return UserResource::collection(User::where("id", $user->id)->get());
    }

    public function notification()
    {
        $user = Auth::user();
        // return Notifications::where('user_id', $user->id)
        // ->orderBy('created_at', 'desc')
        // ->get();
        $notifications = Notifications::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        //Filter out notifications of type 'general' if their associated invitation's status is not 'pending'
        $filteredNotifications = $notifications->filter(function ($notification) {
            if ($notification->type != 'general') {
                $invitation = Invitation::where('id', $notification->invitation_id)->first();
                return $invitation && $invitation->status == 'pending';
            }
            return true;
        });
        return $filteredNotifications;
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        if (Auth::user()->id !== $user->id) {
            return $this->error('', "You not authorized to view the resource");
        }
        return UserResource::collection(User::where("id", Auth::user()->id)->get());
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
