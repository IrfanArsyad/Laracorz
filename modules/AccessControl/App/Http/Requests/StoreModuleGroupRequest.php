<?php

declare(strict_types=1);

namespace Modules\AccessControl\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreModuleGroupRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:module_groups,name'],
            'label' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:50'],
            'order' => ['integer', 'min:0'],
            'active' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'slug grup',
            'label' => 'label grup',
            'icon' => 'ikon',
            'order' => 'urutan',
            'active' => 'status aktif',
        ];
    }
}
