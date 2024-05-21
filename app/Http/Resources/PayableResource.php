<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
      //  return parent::toArray($request);
      return [
        'attributes' => [
            'id' => (string)$this->id,
            'supplierId'=>$this->supplier->id,
            'supplierName'=>$this->supplier->fullname,
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
            'supplier' => $this->supplier,
        ]
        ];
    }
}
