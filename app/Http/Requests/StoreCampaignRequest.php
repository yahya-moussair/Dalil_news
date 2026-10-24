<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'question' => ['required', 'string', 'max:1000'],
            'template_name' => ['nullable', 'string', 'max:80'],
            'template_lang' => ['nullable', 'string', 'max:12'],
            'closes_at' => ['nullable', 'date'],
            'neighborhoods' => ['nullable', 'array'],
            'neighborhoods.*' => ['string', 'max:80'],
            'send' => ['sometimes', 'boolean'],
        ];
    }
}
