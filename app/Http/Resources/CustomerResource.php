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
            'attributes' => [
                'id' => (string)$this->id,
                'name' => $this->fullname,
                'email' => $this->email,
                'phone' => $this->phone,
                'gender' => $this->gender,
                'address' => $this->address,
                'remark' => $this->remark,
                "total_receivables" => $this->receivables->count(),
                'total_receivable_amount' => $this->totalOutstanding() == null ? 0 : $this->totalOutstanding(),
                'total_remaning' => $this->totalRemaining() == null ?  0 : $this->totalRemaining(),
                'crated_at' => $this->crated_at,
                'updated_at' => $this->updated_at

            ],
            'receivables'=>$this->receivables->sortByDesc('created_at')->values()->all(),
            'relationships' => [
                'id' => (string)$this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email
            ]
        ];
    }
    public function totalRemaining()
    {
        $totalRemaining = 0;
        $receivables = $this->receivables;
        foreach ($receivables as $receivable) {
            $totalRemaining += $receivable->remaining;
        }
        return $totalRemaining;

    }
    public function totalOutstanding()
    {
        $totalOutstanding = 0;
        $receivables = $this->receivables;
        foreach ($receivables as $receivable) {
           $totalOutstanding += $receivable->amount;
        }
        return $totalOutstanding;
    }
    // public function receivables()
    // {
    //     $receivables = $this->receivables;
    //     foreach ($receivables as $receivable) {
    //         return ["id" => [$this->$receivable]];
    //     }
    // }
}
