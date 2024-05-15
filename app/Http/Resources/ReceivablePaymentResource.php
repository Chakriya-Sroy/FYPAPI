<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceivablePaymentResource extends JsonResource
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
              'amount'=>$this->amount,
              'remark'=>$this->remark,
              'user_id'=>$this->user_id,
              'receivable_id'=>$this->receivable->id,
              'date'=>$this->date,
              'attachment'=>$this->attachment,
              'created_at'=>$this->created_at,
              'updated_at'=>$this->updated_at
              
            ],
            'relationships'=>[
              "receivable"=>[
                'id'=>(string)$this->receivable->id,
                'amount'=>$this->receivable->amount,
                'remaining'=>$this->receivable->remaining,
                'status'=>$this->receivable->status
              ]
            ]
        ];
    }
}
