<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePayablePayment extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'payable_id'=>'required|exists:payables,id',
            'amount'=>'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'remark'=>'sometimes',
            'attachment'=>'sometimes',
        ];
    }
}
