<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests\Cv;

use App\Application\Cv\ProfileDocumentValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class ProfileTitleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['title' => ['required', 'string']];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (array_diff(array_keys($this->all()), ['title']) !== []) {
                $validator->errors()->add('body', 'The request contains unsupported fields.');
            }
            foreach (ProfileDocumentValidator::validateTitle($this->input('title')) as $field => $messages) {
                foreach ($messages as $message) {
                    $validator->errors()->add($field, $message);
                }
            }
        });
    }
}
