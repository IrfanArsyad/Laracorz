<?php

declare(strict_types=1);

namespace Modules\AccessControl\App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->route('role');
        if ($role instanceof Role && $role->name === Role::SUPER_ADMIN) {
            return false;
        }

        return $this->user()?->hasPermission('update', 'role-management') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $roleId = $this->route('role')?->id;

        return [
            'name' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('roles', 'name')->ignore($roleId)],
            'display_name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'read' => ['array'],
            'create' => ['array'],
            'update' => ['array'],
            'delete' => ['array'],
            'extra' => ['array'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'slug role',
            'display_name' => 'nama tampilan',
            'description' => 'deskripsi',
        ];
    }

    public function messages(): array
    {
        return [
            'name.alpha_dash' => 'Slug role hanya boleh berisi huruf, angka, strip, dan garis bawah.',
        ];
    }
}
