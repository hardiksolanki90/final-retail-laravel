<?php

namespace App\Http\Requests;

use App\Models\Organisation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaxRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Organisation|null $organisation */
        $organisation = Organisation::find($this->user()->organisation_id);
        $profile = $organisation?->resolveTaxProfile();
        $components = $profile['components'] ?? [];

        return [
            'name' => ['required', 'string', 'max:191'],
            'rate' => ['required'],
            'type' => $components !== []
                ? ['required', 'string', Rule::in($components)]
                : ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string'],
        ];
    }
}
