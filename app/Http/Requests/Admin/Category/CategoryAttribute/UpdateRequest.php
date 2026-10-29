<?php

namespace App\Http\Requests\Admin\Category\CategoryAttribute;

use App\Models\Language;
use App\Rules\RootCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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

        $merge = [
            'name' => $name,
        ];

        // Keep value IDs so a rename updates the same row instead of recreating it.
        if (array_key_exists('values', $data)) {
            $values = [];
            foreach ($data['values'] ?? [] as $value) {
                if (!is_array($value)) {
                    continue;
                }

                $valueName = [];
                foreach ($this->locales as $locale) {
                    if (isset($value['name'][$locale])) {
                        $valueName[$locale] = $value['name'][$locale];
                    }
                }

                $entry = [
                    'name' => $valueName,
                ];

                if (isset($value['id']) && $value['id'] !== '') {
                    $entry['id'] = (int) $value['id'];
                }

                if (array_key_exists('color_id', $value)) {
                    $entry['color_id'] = $value['color_id'];
                }

                $values[] = $entry;
            }

            $merge['values'] = $values;
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        $attributeId = $this->route('category_attribute') ?? $this->route('id');

        $rules = [
            'category_id' => ['nullable', 'exists:categories,id', new RootCategory()],
            'type' => 'required|in:square,circle,color',
            'values'      => 'nullable|array',
            'values.*.id' => [
                'nullable',
                'integer',
                Rule::exists('attribute_values', 'id')->where(
                    fn ($query) => $query->where('category_attribute_id', $attributeId)
                ),
            ],
            'values.*.name' => 'nullable|array',
            'values.*.color_id' => ['nullable', 'integer', 'exists:colors,id'],
        ];

        foreach ($this->locales as $locale) {
            $rules["name.$locale"] = 'required|string|max:255';
            $rules["values.*.name.$locale"] = 'required|string|max:255';
        }

        return $rules;
    }
}
