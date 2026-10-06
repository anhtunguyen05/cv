<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests\Cv;

use App\Application\Cv\ProfileDocumentValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class ProfileCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['title' => ['required'], 'personal_information' => ['required', 'array']];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach (ProfileDocumentValidator::validateCreate($this->all()) as $field => $messages) {
                foreach ($messages as $message) {
                    $validator->errors()->add($field, $message);
                }
            }
        });
    }
}
