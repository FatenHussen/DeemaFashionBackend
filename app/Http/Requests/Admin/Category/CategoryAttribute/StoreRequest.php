<?php

namespace App\Http\Requests\Admin\Category\CategoryAttribute;

use App\Models\Language;
use App\Rules\RootCategory;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    protected array $locales = [];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->locales = Language::active()->pluck('code')->toArray();

        $data = $this->all();

        // attribute name
        $name = [];
        foreach ($this->locales as $locale) {
            if (isset($data['name'][$locale])) {
                $name[$locale] = $data['name'][$locale];
            }
        }

        // values names
        $values = [];
        if (!empty($data['values'])) {
            foreach ($data['values'] as $value) {
                $valueName = [];
                foreach ($this->locales as $locale) {
                    if (isset($value['name'][$locale])) {
                        $valueName[$locale] = $value['name'][$locale];
                    }
                }
                $entry = [
                    'name' => $valueName,
                ];

                if (is_array($value) && array_key_exists('color_id', $value)) {
                    $entry['color_id'] = $value['color_id'];
                }

                $values[] = $entry;
            }
        }

        $this->merge([
            'name'   => $name,
            'values' => $values,
        ]);
    }

    public function rules(): array
    {
        $rules = [
            'category_id' => ['required', 'exists:categories,id', new RootCategory()],
            'type' => 'required|in:square,circle,color',
            'values'      => 'nullable|array',
            'values.*.name' => 'required|array',
            'values.*.color_id' => ['nullable', 'integer', 'exists:colors,id'],
        ];

        foreach ($this->locales as $locale) {
            $rules["name.$locale"] = 'required|string|max:255';
            $rules["values.*.name.$locale"] = 'required|string|max:255';
        }

        return $rules;
    }
}
