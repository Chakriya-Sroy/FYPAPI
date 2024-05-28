<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        $currentUserId = Auth::user()->id;

        // $users = User::where('id', '!=', $currentUserId)
        //              ->orderBy('name', 'desc')
        //              ->get();

        // $users = User::where('id', '!=', $currentUserId)
        //      ->whereNotIn('id', function($query) {
        //          $query->select('receiver_id')
        //                ->from('invitations')
        //                ->whereIn('status', ['pending', 'accepted']);
        //      })
        //      ->orderBy('name', 'desc')
        //      ->get();
        // return response()->json($users, 200);
        //Fetch all the user that exclude current user and if any specific user receive invitation form the current user their has_collector_request will be update to true
        // $currentUserId = Auth::id();
        // $users = User::where('users.id', '!=', $currentUserId)
        //     ->leftJoin('invitations', function($join) use ($currentUserId) {
        //         $join->on('users.id', '=', 'invitations.receiver_id')
        //              ->where('invitations.sender_id', '=', $currentUserId)
        //              ->where('invitations.status', 'pending');
        //     })
        //     ->select('users.*', DB::raw('IF(invitations.id IS NOT NULL, 1, 0) as has_collector_request'))
        //     ->orderBy('users.name', 'desc')
        //     ->get();

        // $response = $users->map(function ($user) {
        //     return [
        //         'id' => $user->id,
        //         'name' => $user->name,
        //         'email' => $user->email,
        //         'has_collector_request' => (bool) $user->has_collector_request,
        //     ];
        // });

        // return response()->json($response,200);
        $currentUserId = Auth::id();

        $users = User::where('users.id', '!=', $currentUserId)
            ->leftJoin('invitations', function ($join) use ($currentUserId) {
                $join->on('users.id', '=', 'invitations.receiver_id')
                    ->where('invitations.sender_id', '=', $currentUserId)
                    ->where('invitations.status', 'pending');
            })
            ->select('users.*', DB::raw('IF(invitations.id IS NOT NULL, 1, 0) as has_collector_request'))
            ->orderBy('has_collector_request', 'desc') // Order by has_collector_request
            ->orderBy('users.name', 'desc')
            ->get();

        $response = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'has_collector_request' => (bool) $user->has_collector_request,
            ];
        });

        return response()->json($response, 200);
    }
}
