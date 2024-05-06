<?php

namespace App\Http\Requests;

use DateTime;
use Illuminate\Foundation\Http\FormRequest;

class StoreReceivablePayment extends FormRequest
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
            'receivable_id'=>'required|exists:receivables,id',
            'amount'=>'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'remark'=>'sometimes',
            'attachment'=>'sometimes',
        ];
    }
}
