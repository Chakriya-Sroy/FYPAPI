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
            'id'=>(string)$this->id,
            'attributes'=>[
              'name'=>$this->fullname,
              'email'=>$this->email,
              'phone'=>$this->phone,
              'gender'=>$this->gender,
              'address'=>$this->address,
              'remark'=>$this->remark,
              'crated_at'=>$this->crated_at,
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
