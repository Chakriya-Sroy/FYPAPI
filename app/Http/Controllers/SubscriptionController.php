<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubscriptionRequest;
use App\Models\Subscription;
use App\Traits\HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    use HttpResponse;
    //
    public function store(StoreSubscriptionRequest $request){
        $request->validated($request->all());
        $duration = $request->type == "monthly" ? now()->addDays(30) : now()->addYear(1);
        $price=$request->type =="monthly" ? 2.99 : 33.99;
        $user=Auth::user();
        $subscription= Subscription::create([
            "plan"=>"Premium",
            "type"=>$request->type,
            "start"=>now(),
            "end"=>$duration,
            "price"=>$price,
            "active"=>true,
            "user_id"=>$user->id
        ]);
        return $subscription;
    }
}
