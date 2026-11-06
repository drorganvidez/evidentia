<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** An answer in an open thread, from its author, its audience or a lecturer (the policy). */
class ReplyMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('reply', $this->route('thread'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['body' => ['required', 'string', 'min:2', 'max:20000']];
    }
}
