<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReceivableRequest extends FormRequest
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
            'customer_id'=>'required',
            'amount' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'payment_term'=>'required|in:equaltodueDate,7,15',
            'status' =>'required|in:fullypaid,partiallypaid,outstanding',
            'dueDate' => 'required|date',
            'attachment'=>'sometimes',
            'remark'=>'sometimes'
        ];
    }
}
