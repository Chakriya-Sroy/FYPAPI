<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string)$this->id,
            'attributes' => [
                'name' => $this->fullname,
                'email' => $this->email,
                'phone' => $this->phone,
                'gender' => $this->gender,
                'address' => $this->address,
                'remark' => $this->remark,
                'crated_at' => $this->crated_at,
                'updated_at' => $this->updated_at

            ],
            'relationships' => [
                "owner" => [
                    'id' => (string)$this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email
                ],
               "receivables"=>[
                "receivable"=>$this->receivables,
                "total_receivable" => $this->receivables->count(),
                "total_receivable_amount"=>$this->totalOutstanding(),
                "total_remaining"=>$this->totalRemaining(),
               
               ]
            ]
        ];
    }
    public function totalRemaining(){
        $totalRemaining=0;
        $receivables=$this->receivables;
        foreach($receivables as $receivable){
            return $totalRemaining +=$receivable->remaining;
        }
    }
    public function totalOutstanding(){
        $totalOutstanding=0;
        $receivables=$this->receivables;
        foreach($receivables as $receivable){
            return $totalOutstanding +=$receivable->amount;
        }
    }
    public function receivables(){
        $receivables=$this->receivables;
        foreach ($receivables as $receivable){
            return ["id"=>[$this->$receivable]];
        }
    }
}
