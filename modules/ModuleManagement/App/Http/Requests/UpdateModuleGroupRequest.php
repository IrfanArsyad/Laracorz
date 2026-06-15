<?php

declare(strict_types=1);

namespace Modules\ModuleManagement\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateModuleGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('update', 'module-management') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $groupId = $this->route('group')?->id;

        return [
            'name' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('module_groups', 'name')->ignore($groupId)],
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
        ];
    }
}
