<?php

declare(strict_types=1);

namespace Modules\ModuleManagement\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('create', 'module-management') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'integer', 'exists:modules,id'],
            'module_group_id' => ['nullable', 'integer', 'exists:module_groups,id'],
            'name' => ['required', 'string', 'max:80', 'unique:modules,name'],
            'label' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:50'],
            'url' => ['nullable', 'string', 'max:200'],
            'route_name' => ['nullable', 'string', 'max:200', 'unique:modules,route_name'],
            'badge_source' => ['nullable', 'string', 'max:80'],
            'extra_actions' => ['nullable', 'array'],
            'active' => ['boolean'],
            'external' => ['boolean'],
            'order' => ['integer', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'parent_id' => 'parent modul',
            'module_group_id' => 'grup modul',
            'name' => 'slug modul',
            'label' => 'label',
            'icon' => 'ikon',
            'url' => 'URL',
            'route_name' => 'nama route',
            'badge_source' => 'sumber badge',
            'extra_actions' => 'aksi ekstra',
            'active' => 'status aktif',
            'external' => 'eksternal',
            'order' => 'urutan',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $data = $this->all();
            $hasUrl = ! empty($data['url']);
            $hasRoute = ! empty($data['route_name']);
            if ($hasUrl !== $hasRoute) {
                $validator->errors()->add('url', 'Leaf wajib mengisi url dan route_name. Container kosongkan keduanya.');
            }
            if (empty($data['parent_id']) && empty($data['module_group_id'])) {
                $validator->errors()->add('module_group_id', 'Modul root wajib memiliki grup.');
            }
        });
    }

    public function passedValidation(): void
    {
        // Group hanya di root: kalau ada parent_id, hapus module_group_id
        if ($this->parent_id) {
            $this->merge(['module_group_id' => null]);
        }
    }
}
