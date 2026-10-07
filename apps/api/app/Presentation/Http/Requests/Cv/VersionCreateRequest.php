<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests\Cv;

use App\Application\Cv\ProfileDocumentValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class VersionCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string']];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (array_diff(array_keys($this->all()), ['name']) !== []) {
                $validator->errors()->add('body', 'The request contains unsupported fields.');
            }
            foreach (ProfileDocumentValidator::validateTitle($this->input('name'), 'name') as $field => $messages) {
                foreach ($messages as $message) {
                    $validator->errors()->add($field, $message);
                }
            }
            if (! is_string($this->header('If-Match')) || preg_match('/^"[1-9][0-9]*"$/', (string) $this->header('If-Match')) !== 1) {
                $validator->errors()->add('if_match', 'The If-Match header must contain one strong revision value.');
            }
        });
    }
}
