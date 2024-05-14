<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'attributes'=>[
             'id'=>(string)$this->id,
              'name'=>$this->fullname,
              'email'=>$this->email,
              'phone'=>$this->phone,
              'address'=>$this->address,
              'remark'=>$this->remark,
              'total_payables'=>$this->payables->count(),
              'total_payable_amount'=>$this->totalOutstanding() == null ? 0 : $this->totalOutstanding(),
              'total_remaning'=>$this->totalRemaining()== null ? 0 : $this->totalRemaining(),
              'created_at'=>$this->created_at,
              'updated_at'=>$this->updated_at
              
            ],
            'relationships'=>[
                'id'=>(string)$this->user->id,
                'name'=>$this->user->name,
                'email'=>$this->user->email
            ]
        ];
    }
    public function totalRemaining(){
        $totalRemaining=0;
        $payables=$this->payables;
        foreach($payables as $payable){
            return $totalRemaining +=$payable->remaining;
        }
    }
    public function totalOutstanding(){
        $totalOutstanding=0;
        $payables=$this->payables;
        foreach($payables as $payable){
            return $totalOutstanding +=$payable->amount;
        }
    }
    public function payables(){
        $payables=$this->payables;
        foreach ($payables as $payable){
            return ["id"=>[$this->$payable]];
        }
    }
}
