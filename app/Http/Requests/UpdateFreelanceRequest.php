<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFreelanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'nom' => ['sometimes', 'string', 'max:255'],
            'prenom' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user?->id],
            'telephone' => ['nullable', 'regex:/^[0-9+\-(). ]+$/u', 'max:20'],
            'taux_horaire' => ['nullable', 'numeric', 'min:0', 'max:100000'],
        ];
    }
}
