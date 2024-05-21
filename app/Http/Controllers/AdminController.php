<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index() {
        $currentUserId = Auth::user()->id;

        $users = User::where('id', '!=', $currentUserId)
                     ->orderBy('name', 'desc')
                     ->get();

        return response()->json($users, 200);
    }
}
