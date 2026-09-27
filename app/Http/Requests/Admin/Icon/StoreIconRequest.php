<?php

namespace App\Http\Requests\Admin\Icon;

use App\Rules\IconImage;
use Illuminate\Foundation\Http\FormRequest;

class StoreIconRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|array',
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'image' => ['required', 'file', 'max:2048', new IconImage],
            'description' => 'nullable|array',
            'description.ar' => 'nullable|string|max:1000',
            'description.en' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم الأيقونة مطلوب',
            'name.array' => 'اسم الأيقونة يجب أن يكون مصفوفة تحتوي على اللغات',
            'name.ar.required' => 'اسم الأيقونة بالعربي مطلوب',
            'name.ar.string' => 'اسم الأيقونة بالعربي يجب أن يكون نص',
            'name.ar.max' => 'اسم الأيقونة بالعربي يجب ألا يتجاوز 255 حرف',
            'name.en.required' => 'اسم الأيقونة بالإنجليزي مطلوب',
            'name.en.string' => 'اسم الأيقونة بالإنجليزي يجب أن يكون نص',
            'name.en.max' => 'اسم الأيقونة بالإنجليزي يجب ألا يتجاوز 255 حرف',
            'image.required' => 'صورة الأيقونة مطلوبة',
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
