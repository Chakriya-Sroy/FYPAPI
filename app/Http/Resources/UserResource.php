<?php

namespace App\Http\Resources;

use App\Models\Collector;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_info' => [
                'id'=>$this->id,
                'name' => $this->name,
                'email' => $this->email,
                'has_collector_role' => $this->hasCollectorRole(),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated
            ],
            'customers' => $this->customers->count(),
            'assign_customers' =>  $this->assignedCustomers->count(),
            'receivables' => [
                'total_receivables' => $this->receivables->count(),
                'total_outstanding'=>$this->totalReceivableOutstanding(),
                'total_remaining'=>$this->totalReceivableRemining(),
            ],
            'suppliers'=>$this->suppliers->count(),
            'payables'=>[
                'total_payables'=>$this->payables->count(),
                'total_outstanding'=>$this->totalPayableOutstanding(),
                'total_remaining'=>$this->totalPayableRemining()
            ],
            'collector' => [
                'has_collector'=>$this->hasCollector(),
                'collector_info'=>$this->getCollector()
            ],
            'subscription'=> !$this->subscription ? ["type"=>"free"] :[
                "type"=> $this->subscription->type,
                "start"=> $this->subscription->start,
                "end"=> $this->subscription->end,
                "status" =>$this->subscription->active == true ? "active" :"unactive"
            ]
        ];
    }
    public function hasCollectorRole()
    {
        $hasCollectorRole = false;
        foreach ($this->roles as $role) {
            if ($role->role_id == 2) {
                $hasCollectorRole = true;
                break; // Exit the loop once collector role is found
            }
        }
        return $hasCollectorRole;
    }
    public function hasCollector()
    {
        $collectorId = Collector::where('user_id', $this->id)->get();
        return $collectorId->count() > 0;
    }
    public function getCollector()
    {
        $collectorId = Collector::where('user_id', $this->id)->first();
        if ($collectorId) {
            return User::findOrFail($collectorId->collector_id);
        }
        return null;
    }
    public function totalReceivableOutstanding(){
        $receivables=$this->receivables;
        $total=$receivables->where('isArchive',false)->sum('amount');
        return $total;
    }
    public function totalReceivableRemining(){
        $receivables=$this->receivables;
        $remaining=$receivables->where('isArchive',false)->sum('remaining');
        return $remaining;
    }
    public function totalPayableOutstanding(){
        $payables=$this->payables;
        $total = $payables->where('isArchive', false)->sum('amount');
        return $total;
    }
    public function totalPayableRemining(){
        $payables=$this->payables;
        $remaining=$payables->where('isArchive',false)->sum('remaining');
        return $remaining;
    }
}
