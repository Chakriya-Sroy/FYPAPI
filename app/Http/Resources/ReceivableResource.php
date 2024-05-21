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
            'attributes' => [
                'id' => (string)$this->id,
                'customerId'=>$this->customer->id,
                'customerName'=>$this->customer->fullname,
                'amount' => $this->amount,
                'remaining' => $this->remaining,
                'payment_term' => $this->payment_term,
                'status' => $this->status,
                'date' => $this->date,
                'dueDate' => $this->dueDate,
                'remark' => $this->remark,
                'attachment' => $this->attachment,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at

            ],
            'relationships' => [
                'payment'=>$this->payments->sortByDesc('created_at')->values()->all(),
                'customer' => $this->customer,
            ]
        ];
    }
}
