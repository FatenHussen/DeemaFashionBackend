<?php

namespace App\Services\User;

use App\Exceptions\CustomExceptionWithMessage;
use App\Models\Shop;
use App\Models\ShopProductVariant;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CheckoutPolicyService
{
    /**
     * One cart, one limit. Deema platform reads dashboard settings (API keys still tikmart_*).
     * A shop uses its own fields.
     * A restaurant-only cart is instant: restaurant drivers or Deema couriers.
     * A mixed cart still returns one limit, the strictest. Restaurant hours do not extend it.
     */
    public function forShops(Collection $shops): array
    {
        $shops = $shops->unique('id')->values();

        if ($shops->isEmpty()) {
            return $this->platform();
        }

        $instantOnly = $shops->every(fn (Shop $shop): bool => $this->isRestaurant($shop));

        $policies = $shops->map(function (Shop $shop) {
            if ($shop->is_default) {
                return $this->platform();
            }

            $policy = $this->fromShop($shop);
            if ($this->isRestaurant($shop)) {
                $policy['delivery_min_hours'] = 0;
                $policy['delivery_max_hours'] = 0;
            }

            return $policy;
        });

        $policy = $policies->count() === 1
            ? $policies->first()
            : $this->strictest($policies);

        if ($instantOnly) {
            $policy['source'] = 'restaurant';
            $policy['delivery_min_hours'] = 0;
            $policy['delivery_max_hours'] = 0;
            $policy['instant_only'] = true;
            $policy['fulfillment'] = 'restaurant_or_tikmart';

            return $policy;
        }

        $policy['instant_only'] = false;
        $policy['fulfillment'] = null;

        return $policy;
    }

    private function isRestaurant(Shop $shop): bool
    {
        return (bool) $shop->is_restaurant && ! $shop->is_default;
    }

    private function strictest(Collection $policies): array
    {
        $minHours = (int) $policies->max('delivery_min_hours');
        $maxHours = (int) $policies->max('delivery_max_hours');

        return [
            'source' => 'shop',
            'shop_id' => null,
            'min_order_amount' => (float) $policies->max('min_order_amount'),
            'delivery_min_hours' => $minHours,
            'delivery_max_hours' => max($maxHours, $minHours),
        ];
    }

    public function shopsFromVariantIds(array $variantIds): Collection
    {
        $ids = collect($variantIds)->filter()->unique()->values();
        if ($ids->isEmpty()) {
            return collect();
        }

        $shopIds = ShopProductVariant::query()
            ->whereIn('id', $ids)
            ->pluck('shop_id')
            ->filter()
            ->unique()
            ->values();

        if ($shopIds->isEmpty()) {
            return collect();
        }

        return Shop::query()->whereIn('id', $shopIds)->get();
    }

    public function summarize(array $policy, float $subtotal): array
    {
        $minimum = round((float) $policy['min_order_amount'], 2);
        $subtotal = round($subtotal, 2);
        $remaining = round(max(0, $minimum - $subtotal), 2);

        return array_merge($policy, [
            'subtotal' => $subtotal,
            'remaining_amount' => $remaining,
            'can_checkout' => $remaining <= 0,
            'earliest_delivery_at' => $this->earliestAt($policy)->format('Y-m-d H:i'),
        ]);
    }

    public function resolveChoice(array $data): string
    {
        $choice = $data['delivery_choice'] ?? null;
        if ($choice === 'scheduled' || $choice === 'asap') {
            return $choice;
        }

        return filter_var($data['is_instant_delivery'] ?? false, FILTER_VALIDATE_BOOLEAN)
            ? 'asap'
            : 'scheduled';
    }

    public function assertDeliveryChoice(string $choice, ?string $scheduledAt, array $policy): void
    {
        if ($choice === 'asap') {
            return;
        }

        if ($scheduledAt === null || trim($scheduledAt) === '') {
            throw new CustomExceptionWithMessage('custom.checkout.delivery_time_required', 422);
        }

        $when = Carbon::parse($scheduledAt)->seconds(0);
        $earliest = $this->earliestAt($policy);

        if ($when->lt($earliest)) {
            throw new CustomExceptionWithMessage('custom.checkout.delivery_too_soon', 422, [
                'earliest' => $earliest->format('Y-m-d H:i'),
            ]);
        }
    }

    public function earliestAt(array $policy): Carbon
    {
        return now()->addHours((int) $policy['delivery_min_hours'])->seconds(0);
    }

    public function platform(): array
    {
        $minHours = (int) SystemSetting::get('tikmart_delivery_min_hours', 24);
        $maxHours = (int) SystemSetting::get('tikmart_delivery_max_hours', 48);

        return [
            'source' => 'tikmart',
            'shop_id' => null,
            'min_order_amount' => round((float) SystemSetting::get('tikmart_min_order_amount', 0), 2),
            'delivery_min_hours' => max(0, $minHours),
            'delivery_max_hours' => max($maxHours, $minHours),
            'instant_only' => false,
            'fulfillment' => null,
        ];
    }

    public function fromShop(Shop $shop): array
    {
        $platform = $this->platform();
        $minHours = $shop->delivery_min_hours !== null
            ? (int) $shop->delivery_min_hours
            : $platform['delivery_min_hours'];
        $maxHours = $shop->delivery_max_hours !== null
            ? (int) $shop->delivery_max_hours
            : $platform['delivery_max_hours'];

        return [
            'source' => 'shop',
            'shop_id' => $shop->id,
            'min_order_amount' => $shop->min_order_amount !== null
                ? round((float) $shop->min_order_amount, 2)
                : $platform['min_order_amount'],
            'delivery_min_hours' => max(0, $minHours),
            'delivery_max_hours' => max($maxHours, $minHours),
            'instant_only' => false,
            'fulfillment' => null,
        ];
    }
}
