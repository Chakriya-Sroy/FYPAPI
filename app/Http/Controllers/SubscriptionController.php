<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubscriptionRequest;
use App\Models\Subscription;
use App\Traits\HttpResponse;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    use HttpResponse;
    //
    public function store(StoreSubscriptionRequest $request)
    {
        $request->validated($request->all());
        $duration = $request->type == "monthly" ? now()->addDays(30) : now()->addYear(1);
        $price = $request->type == "monthly" ? 2.99 : 33.99;
        $user = Auth::user();
        if (!$user->subscription) {
            $subscription = Subscription::create([
                "plan" => "Premium",
                "type" => $request->type,
                "start" => now(),
                "end" => $duration,
                "price" => $price,
                "active" => true,
                "user_id" => $user->id
            ]);
            return response()->json($subscription, 200);
        }
        
        return $this->error("", "Your subscription haven't end yet");
    }
    public function update()
    {
        // Update from monthly to yearly
        $user = Auth::user();
        if ($user->id != $user->subscription->user_id) {
            return $this->error("", "You not authorize to update this resource");
        }
       
        if ($user->subscription->type != "yearly") {
            $duration = now()->addYear(1);
            $price = 33.99;    
            $endDate =$user->subscription->end;
            $remaingingDayleft=now()->diffInDays($endDate);
            $newSubscription = $user->subscription->update([
                "plan" => "Premium",
                "type" => 'yearly',
                "start" => now(),
                "end" => $duration->addDays($remaingingDayleft),
                "price" => $price,
                "active" => true,
                "user_id" => $user->id
            ]);

            return $this->success($newSubscription, "Your subscription's plan has been update");
            
        }
        return $this->error('','You yearly premuim plan still active');
    }

    public function continue(StoreSubscriptionRequest $request){
        //update the plan after expire
        $user = Auth::user();
        if ($user->id != $user->subscription->user_id) {
            return $this->error("", "You not authorize to continue this resource");
        }
        if(!$user->subscription->active){
            $request->validated($request->all());
            $duration = $request->type == "monthly" ? now()->addDays(30) : now()->addYear(1);
            $price = $request->type == "monthly" ? 2.99 : 33.99;
            $newSubscription = $user->subscription->update([
                "plan" => "Premium",
                "type" => $request->type,
                "start" => now(),
                "end" => $duration,
                "price" => $price,
                "active" => true,
                "user_id" => $user->id
            ]);
            return $this->success($newSubscription, "Your subscription's plan has been update");  
        }
    }
}
