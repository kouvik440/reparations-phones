<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('users.manage') === true
            && (! $this->route('user') || $this->user()->canManageAccount($this->route('user')));
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'email' => is_string($this->email) ? Str::lower(trim($this->email)) : $this->email,
        ]);
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('users', 'name')->ignore($user?->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', 'string', Rule::exists('roles', 'slug'), function (string $attribute, mixed $value, \Closure $fail): void {
                $role = \App\Models\Role::where('slug', $value)->first();
                if ($role && ! $this->user()->canAssignRole($role)) {
                    $fail('Vous ne pouvez pas attribuer un rôle disposant de permissions supérieures aux vôtres.');
                }
            }],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'max:72', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Indiquez le nom de l’utilisateur.',
            'name.unique' => 'Ce nom est déjà utilisé.',
            'email.required' => 'L’email est obligatoire pour se connecter.',
            'email.email' => 'Indiquez une adresse email valide.',
            'email.unique' => 'Cette adresse email est déjà associée à un compte.',
            'password.required' => 'Définissez un mot de passe pour ce compte.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.max' => 'Le mot de passe ne doit pas dépasser 72 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'role.in' => 'Sélectionnez un rôle valide.',
        ];
    }
}
