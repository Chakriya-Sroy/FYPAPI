<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SubscriptionActiveStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:subscription';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'User Subscription Status Update';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $alluser =User::all();
        foreach($alluser as $user){
            $subscription =$user->subscription;
            if($subscription){
                $end =$subscription->end;
                $difference = now()->diffInDays($end, false);
                if($difference==0){
                    $subscription->active =false;
                    $subscription->save();
                }
            }
          
        }
        Log::info($difference);
    }
}
