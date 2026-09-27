<?php

namespace App\Http\Requests\User\BasketSchedule;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('user')->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            // 'category_id' => ['nullable', 'exists:categories,id'],

            'schedule_id' => ['required', 'exists:schedules,id'],

            'start_date' => ['required', 'date', 'after_or_equal:today'],

            'delivery_time' => ['nullable', 'date_format:H:i'],

            'is_active' => ['sometimes', 'boolean'],

            'items' => ['required', 'array', 'min:1'],

            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.shop_product_variant_id' => ['nullable', 'exists:shop_product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id'   => auth('user')->id(),
            'is_active' => $this->is_active ?? true,
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('items', []) as $index => $item) {
                if (empty($item['product_id']) && empty($item['shop_product_variant_id'])) {
                    $validator->errors()->add(
                        "items.$index.shop_product_variant_id",
                        'معرّف المنتج أو متغيّر المتجر مطلوب',
                    );
                }
            }

            $date = $this->input('start_date');
            $time = $this->input('delivery_time');
            if (!$date || !$time || $validator->errors()->isNotEmpty()) {
                return;
            }

            $at = \Carbon\Carbon::parse($date.' '.$time);
            if ($at->lt(now())) {
                $validator->errors()->add('delivery_time', 'وقت التوصيل لازم يكون من الآن فصاعداً');
            }
        });
    }
}
