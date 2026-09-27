<?php

namespace App\Http\Requests\Admin\Icon;

use App\Rules\IconImage;
use Illuminate\Foundation\Http\FormRequest;

class UpdateIconRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->files->has('icon') && ! $this->files->has('image')) {
            $this->files->set('image', $this->files->get('icon'));
            (function () {
                $this->convertedFiles = null;
            })->call($this);
        }

        if ($this->hasFile('image')) {
            return;
        }

        // The edit form sends the current URL back as text. That is not a new file.
        $this->request->remove('image');
        $this->request->remove('icon');
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|array',
            'name.ar' => 'required_with:name|string|max:255',
            'name.en' => 'required_with:name|string|max:255',
            'image' => ['nullable', 'file', 'max:2048', new IconImage],
            'description' => 'nullable|array',
            'description.ar' => 'nullable|string|max:1000',
            'description.en' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.array' => 'اسم الأيقونة يجب أن يكون مصفوفة تحتوي على اللغات',
            'name.ar.required_with' => 'اسم الأيقونة بالعربي مطلوب',
            'name.ar.string' => 'اسم الأيقونة بالعربي يجب أن يكون نص',
            'name.ar.max' => 'اسم الأيقونة بالعربي يجب ألا يتجاوز 255 حرف',
            'name.en.required_with' => 'اسم الأيقونة بالإنجليزي مطلوب',
            'name.en.string' => 'اسم الأيقونة بالإنجليزي يجب أن يكون نص',
            'name.en.max' => 'اسم الأيقونة بالإنجليزي يجب ألا يتجاوز 255 حرف',
            'image.image' => 'الملف يجب أن يكون صورة',
            'image.mimes' => 'صيغة الصورة يجب أن تكون: jpeg, png, jpg, gif, svg, webp',
            'image.max' => 'حجم الصورة يجب ألا يتجاوز 2MB',
            'description.array' => 'الوصف يجب أن يكون مصفوفة تحتوي على اللغات',
            'description.ar.string' => 'الوصف بالعربي يجب أن يكون نص',
            'description.ar.max' => 'الوصف بالعربي يجب ألا يتجاوز 1000 حرف',
            'description.en.string' => 'الوصف بالإنجليزي يجب أن يكون نص',
            'description.en.max' => 'الوصف بالإنجليزي يجب ألا يتجاوز 1000 حرف',
            'is_active.boolean' => 'حالة الأيقونة يجب أن تكون صحيح أو خطأ',
        ];
    }
}
