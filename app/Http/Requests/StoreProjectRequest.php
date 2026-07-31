<?php

namespace App\Http\Requests;

use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => [
                'required',
                'exists:clients,id',
                function ($attribute, $value, $fail) {
                    if (! Client::where('id', $value)->where('user_id', $this->user()->id)->exists()) {
                        $fail('Le client sélectionné ne vous appartient pas.');
                    }
                },
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}
