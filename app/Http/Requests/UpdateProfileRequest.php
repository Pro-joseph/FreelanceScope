<?php

namespace App\Http\Requests;

use App\Enums\UserStatut;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['sometimes', 'string', 'max:255'],
            'prenom' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$this->user()->id],
            'telephone' => ['nullable', 'regex:/^[0-9+\-(). ]+$/u', 'max:20'],
            'taux_horaire' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'statut' => ['sometimes', new Enum(UserStatut::class)],
        ];
    }
}
