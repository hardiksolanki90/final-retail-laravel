<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReasonTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'type' => ['required', 'in:Non Service Reason,Good Return Reason,Bad Return Reason,Debit Note Reason,Visit Reason,Receipt Reason,Order,Delivery,CreditNote,SalesmanLoad,GoodReturnNote,Order Process Reason,Delivery Reason'],
            'code' => ['nullable', 'string', 'max:191'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
