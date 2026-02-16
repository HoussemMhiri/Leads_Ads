<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class InviteEmployeesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'emails' => 'required|array|min:1|max:20',
            'emails.*' => 'required|email|distinct',
            'role' => 'nullable|string|in:admin,member,viewer',
        ];
    }

    public function messages(): array
    {
        return [
            'emails.required' => 'At least one email address is required.',
            'emails.*.email' => 'Each entry must be a valid email address.',
            'emails.*.distinct' => 'Duplicate email addresses are not allowed.',
            'emails.max' => 'You can invite up to 20 employees at a time.',
        ];
    }
}
