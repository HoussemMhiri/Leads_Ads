<?php

namespace App\Http\Requests\Workspace;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkspaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'min:1', 'max:255'],
            'logo' => ['sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->has('name') && ! $this->hasFile('logo')) {
                $validator->errors()->add('general', 'Provide at least a name or a logo to update.');
            }
        });
    }
}
