<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests\Cv;

use App\Application\Cv\ProfileDocumentValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class ProfileSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $section = (string) $this->route('section');
            if (! in_array($section, ProfileDocumentValidator::sections(), true)) {
                $validator->errors()->add('section', 'The selected section is invalid.');

                return;
            }
            if (array_diff(array_keys($this->all()), [$section]) !== [] || ! array_key_exists($section, $this->all())) {
                $validator->errors()->add('body', 'The request must contain exactly the selected section.');

                return;
            }
            foreach (ProfileDocumentValidator::validateSection($section, $this->input($section)) as $field => $messages) {
                foreach ($messages as $message) {
                    $validator->errors()->add($field, $message);
                }
            }
        });
    }
}
