<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChannelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channelName' => ['required_without:name', 'nullable', 'string', 'max:191'],
            'name' => ['required_without:channelName', 'nullable', 'string', 'max:191'],
            'parentId' => ['nullable', 'integer'],
            'nodeLevel' => ['nullable', 'integer'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
