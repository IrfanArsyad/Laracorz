<?php

declare(strict_types=1);

namespace Modules\AccessControl\App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('update', 'user-management') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($userId)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'status' => ['required', Rule::in([User::STATUS_ACTIVE, User::STATUS_INACTIVE, User::STATUS_BANNED])],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'role_id' => 'role',
            'name' => 'nama',
            'username' => 'username',
            'email' => 'email',
            'password' => 'kata sandi',
            'password_confirmation' => 'konfirmasi kata sandi',
            'status' => 'status',
            'avatar' => 'foto profil',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, strip, dan garis bawah.',
        ];
    }
}
