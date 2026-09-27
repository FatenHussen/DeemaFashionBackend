<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class IconImage implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            $fail('الملف يجب أن يكون صورة');

            return;
        }

        $extension = strtolower($value->getClientOriginalExtension());

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'], true)) {
            $fail('صيغة الصورة يجب أن تكون: jpeg, png, jpg, gif, svg, webp');
        }
    }
}
