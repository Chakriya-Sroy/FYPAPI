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
            'id'=>(string)$this->id,
            'attributes'=>[
              'name'=>$this->fullname,
              'email'=>$this->email,
              'phone'=>$this->phone,
              'address'=>$this->address,
              'remark'=>$this->remark,
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
}
