<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Models\Setting;
use Illuminate\Validation\Rule;

class StoreSettingRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Setting::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:150', Rule::unique('settings', 'key')->whereNull('deleted_at')],
            'value' => ['nullable', 'string'],
            'type' => ['required', 'string', 'in:string,number,boolean,json,encrypted'],
            'group' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'isEncrypted' => ['sometimes', 'boolean'],
        ];
    }
}
