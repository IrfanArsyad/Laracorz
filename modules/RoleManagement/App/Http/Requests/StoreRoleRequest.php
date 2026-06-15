<?php

declare(strict_types=1);

namespace Modules\RoleManagement\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('create', 'role-management') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:roles,name'],
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

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'slug role',
            'display_name' => 'nama tampilan',
            'description' => 'deskripsi',
            'is_active' => 'status aktif',
            'read' => 'izin baca',
            'create' => 'izin tambah',
            'update' => 'izin ubah',
            'delete' => 'izin hapus',
            'extra' => 'izin ekstra',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.alpha_dash' => 'Slug role hanya boleh berisi huruf, angka, strip, dan garis bawah.',
        ];
    }
}
