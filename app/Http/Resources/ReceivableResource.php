<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceivableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>(string)$this->id,
            'attributes'=>[
              'amount'=>$this->amount,
              'remaining'=>$this->remaining,
              'payment_term'=>$this->payment_term,
              'status'=>$this->status,
              'date'=>$this->date,
              'dueDate'=>$this->dueDate,
              'remark'=>$this->remark,
              'attachment'=>$this->attachment,
              'crated_at'=>$this->crated_at,
              'updated_at'=>$this->updated_at
              
            ],
            'relationships'=>[
                'id'=>(string)$this->customer->id,
                'name'=>$this->customer->fullname,
                'email'=>$this->customer->email
            ]
        ];
    }
}
