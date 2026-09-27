<?php

namespace App\Http\Requests\User\Order;

use App\Enums\CartType;
use App\Models\Promotion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * أنواع العروض التي يمرّرها المستخدم عبر promotion_id (خصم فقط).
     * العروض التلقائية (هدية، نقاط، توصيل مجاني) لا تُحسب هنا.
     */
    private const PROMOTION_TYPES_DISCOUNT_CONFLICT = [
        'simple_discount',
        'spend_x_discount',
    ];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'address_id' => ['required', 'exists:user_addresses,id'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'cart_type' => [
                'nullable',
                'string',
                Rule::in(array_map(fn (CartType $case) => $case->value, CartType::cases())),
            ],
            'delivery_choice' => ['nullable', 'string', Rule::in(['asap', 'scheduled'])],
            'is_instant_delivery' => ['required_without:delivery_choice', 'boolean'],
            'scheduled_delivery_at' => ['nullable', 'date'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.shop_product_variant_id' => [
                'required',
                'exists:shop_product_variants,id'
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.note' => ['nullable', 'string', 'max:500'],
            'items.*.extras' => ['nullable', 'array'],
            'items.*.extras.*.id' => ['required', 'integer', 'exists:product_extra_details,id'],
            'items.*.extras.*.quantity' => ['required', 'integer', 'min:1'],
            'recipe_id' => 'nullable|exists:recipes,id',
            'admin_basket_id' => 'nullable|exists:baskets,id',
            'admin_schedule_basket_id' => 'nullable|exists:baskets,id',
            'basket_schedule_id' => 'nullable|exists:basket_schedules,id',
            'coupon' => 'nullable|string',
            'affiliate_id' => 'nullable|string',

            // Point exchanges
            'point_coupon_exchange_id' => 'nullable|integer|exists:point_exchanges,id',
            'point_free_delivery_exchange_id' => 'nullable|integer|exists:point_exchanges,id',

            // Subscription benefits (user choice)
            'use_subscription_discount' => 'nullable|boolean',
            'use_subscription_free_delivery' => 'nullable|boolean',
            //add schedule

            'promotion_id' => ['nullable', 'integer', 'exists:promotions,id'],
        ];
    }

    /**
     * Configure the validator instance.
     */

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $data = $this->all();

            /** promotion_id: خصم يختاره المستخدم فقط (simple_discount / spend_x_discount) */
            if (! empty($data['promotion_id'])) {
                $promotion = Promotion::find($data['promotion_id']);
                if (
                    $promotion
                    && ! in_array($promotion->type, ['simple_discount', 'spend_x_discount'], true)
                ) {
                    $validator->errors()->add(
                        'promotion_id',
                        'يمكن تمرير عرض خصم فقط (simple_discount أو spend_x_discount).'
                    );
                }
            }

            /** ---------------------------------
             * 1️⃣ التحقق من تضارب مصادر الخصم
             * --------------------------------- */
            $discountSources = 0;

            if (!empty($data['coupon'])) {
                $discountSources++;
            }

            if (!empty($data['point_coupon_exchange_id'])) {
                $discountSources++;
            }

            if (!empty($data['use_subscription_discount'])) {
                $discountSources++;
            }

            if (!empty($data['promotion_id'])) {

                $promotion = Promotion::find($data['promotion_id']);

                if ($promotion && in_array($promotion->type, self::PROMOTION_TYPES_DISCOUNT_CONFLICT, true)) {
                    $discountSources++;
                }
            }

            if ($discountSources > 1) {
                $validator->errors()->add(
                    'discount_conflict',
                    'يمكن استخدام مصدر خصم واحد فقط: كوبون أو نقاط أو باقة أو عرض'
                );
            }

            /** ---------------------------------
             * 2️⃣ التحقق من تضارب التوصيل المجاني
             * --------------------------------- */
            $freeDeliverySources = 0;

            if (!empty($data['point_free_delivery_exchange_id'])) {
                $freeDeliverySources++;
            }

            if (!empty($data['use_subscription_free_delivery'])) {
                $freeDeliverySources++;
            }

            if ($freeDeliverySources > 1) {
                $validator->errors()->add(
                    'free_delivery_conflict',
                    'يمكن استخدام مصدر توصيل مجاني واحد فقط: نقاط أو باقة'
                );
            }
        });
    }
}
