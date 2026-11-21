<?php

namespace App\Services\Admin;

use App\Exceptions\CustomExceptionWithMessage;
use App\Models\Basket;

class ToggleStatusService
{
    protected const MODEL_MAP = [
        'activity_log' => \App\Models\ActivityLog::class,
        'admin' => \App\Models\Admin::class,
        'affiliate_withdraw_request' => \App\Models\AffiliateWithdrawRequest::class,
        'area' => \App\Models\Area::class,
        'badge' => \App\Models\Badge::class,
        'banner' => \App\Models\Banner::class,
        'basket' => Basket::class,
        'basket_schedule' => \App\Models\BasketSchedule::class,
        'brand' => \App\Models\Brand::class,
        'category' => \App\Models\Category::class,
        'category_attribute' => \App\Models\CategoryAttribute::class,
        'category_detail' => \App\Models\CategoryDetail::class,
        'city' => \App\Models\City::class,
        'color' => \App\Models\Color::class,
        'coupon' => \App\Models\Coupon::class,
        'country' => \App\Models\Country::class,
        'currency' => \App\Models\Currency::class,
        'driver' => \App\Models\Driver::class,
        'faq' => \App\Models\Faq::class,
        'gift' => \App\Models\Gift::class,
        'governorate' => \App\Models\Governorate::class,
        'icon' => \App\Models\Icon::class,
        'language' => \App\Models\Language::class,
        'media' => \App\Models\Media::class,
        'package' => \App\Models\Package::class,
        'page_section' => \App\Models\PageSection::class,
        'payment_method' => \App\Models\PaymentMethod::class,
        'point_exchange' => \App\Models\PointExchange::class,
        'point_rule' => \App\Models\PointRule::class,
        'point_transaction' => \App\Models\PointTransaction::class,
        'point_wallet' => \App\Models\PointWallet::class,
        'product' => \App\Models\Product::class,
        'product_category_detail' => \App\Models\ProductCategoryDetail::class,
        'product_extra_detail' => \App\Models\ProductExtraDetail::class,
        'product_media' => \App\Models\ProductMedia::class,
        'product_variant' => \App\Models\ProductVariant::class,
        'promotion' => \App\Models\Promotion::class,
        'promotion_request' => \App\Models\PromotionRequest::class,
        'recipe' => \App\Models\Recipe::class,
        'role' => \App\Models\Role::class,
        'sale_country' => \App\Models\SaleCountry::class,
        'schedule' => \App\Models\Schedule::class,
        'section' => \App\Models\Section::class,
        'seller_registration' => \App\Models\SellerRegistration::class,
        'service' => \App\Models\Service::class,
        'setting' => \App\Models\Setting::class,
        'shop' => \App\Models\Shop::class,
        'store_user' => \App\Models\StoreUser::class,
        'subscription' => \App\Models\Subscription::class,
        'system_setting' => \App\Models\SystemSetting::class,
        'user' => \App\Models\User::class,
        'user_basket_schedule' => \App\Models\UserBasketSchedule::class,
        'vendor' => \App\Models\Vendor::class,
        'vendor_package' => \App\Models\VendorPackage::class,
        'vendor_subscription' => \App\Models\VendorSubscription::class,
        'vendor_user' => \App\Models\VendorUser::class,
    ];

    protected array $modelMap = self::MODEL_MAP;

    public static function allowedTypes(): array
    {
        return array_keys(self::MODEL_MAP);
    }

    /**
     * Toggle is_active status for a model
     *
     * @param string $type
     * @param int $id
     * @param bool $isActive
     * @return array
     * @throws CustomExceptionWithMessage
     */
    public function toggleStatus(string $type, int $id, bool $isActive): bool
    {
        if ($type === 'currency') {
            app(CurrencyService::class)->update($id, ['is_active' => $isActive]);

            return true;
        }

        // Get model class from map
        if (!isset($this->modelMap[$type])) {
            throw new CustomExceptionWithMessage(__('custom.invalid_model_type'));
        }

        $modelClass = $this->modelMap[$type];

        // Find the record
        $record = $modelClass::find($id);

        if (!$record) {
            throw new CustomExceptionWithMessage(__('custom.record_not_found'));
        }

        // Update is_active status
        $record->is_active = $isActive;
        $record->save();

        return true;
    }
}
