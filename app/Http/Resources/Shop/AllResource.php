<?php

namespace App\Http\Resources\Shop;

use App\Http\Resources\Area\OneResource as AreaOneResource;
use App\Http\Resources\Vendor\AllResource as VendorAllResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Badge\OneResource as BadgeOneResource;

class AllResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $coverImages = $this->getCoverImagesUrls();

        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'description'           => $this->description,
            'address'               => $this->address,
            'email'                 => $this->email,
            'mobile'                => $this->mobile,
            'logo_url'              => $this->logo_url,
            'cover_image'           => $coverImages[0] ?? null,
            'is_active'             => $this->is_active,
            'is_open_now'           => $this->isOpenNow(),
            'is_service_provider'   => (bool) $this->is_service_provider,
            'is_restaurant'         => (bool) $this->is_restaurant,
            'is_default'            => (bool) $this->is_default,
            'min_order_amount'      => $this->min_order_amount !== null ? (float) $this->min_order_amount : null,
            'delivery_min_hours'    => $this->delivery_min_hours,
            'delivery_max_hours'    => $this->delivery_max_hours,
            'payment_methods'       => $this->payment_methods ?? [],
            'pricing_tier'          => $this->pricing_tier,
            'is_recommended'        => (bool) $this->is_recommended,
            'created_at'            => $this->created_at?->format('Y-m-d H:i'),
            'is_favorite'           => (bool) ($this->is_favorite ?? false),
            'average_rating'        => $this->average_rating ?? 0,
            // 'ratings_count'      => $this->ratings_count,
            'categories'            => $this->getShopCategories(),
            'vendor'                => VendorAllResource::make($this->vendor),
            'top_badges' => BadgeOneResource::collection(
                $this->badges->where('position', 'top')->values()
            ),

            'bottom_badges' => BadgeOneResource::collection(
                $this->badges->where('position', 'bottom')->values()
            ),
           'restaurant_meta'        => $this->when($this->is_restaurant, function () use ($request) {
                $deliveryTimeRange = $this->resolveDeliveryTimeRange();
                $distanceKm = $this->resolveDistanceKm($request);

                return [
                    'delivery_time_range' => $deliveryTimeRange,
                    'location_label' => $this->resolveLocationLabel(),
                    'distance_km' => $distanceKm,
                    'delivery_fee' => $this->resolveDeliveryFee(),
                    'is_free_delivery' => (bool) $this->is_free_delivery,
                ];
            }),

        ];
    }
    private function resolveDeliveryTimeRange(): ?string
    {
        if (!$this->relationLoaded('productVariants')) {
            return null;
        }

        return $this->productVariants
            ->map(fn($variant) => $variant->productVariant?->product?->effective_delivery_time)
            ->filter()
            ->unique()
            ->first();
    }

    private function resolveLocationLabel(): ?string
    {
        $city = $this->area?->city?->name;
        $area = $this->area?->name;

        if ($city && $area) {
            return "{$city}, {$area}";
        }

        return $area ?: $this->address;
    }

    private function resolveDistanceKm(Request $request): ?float
    {
        $lat = $request->query('lat');
        $lng = $request->query('lng');

        if (!$lat || !$lng || !$this->lat || !$this->lng) {
            return null;
        }

        $distance = $this->calculateDistanceKm((float) $lat, (float) $lng, (float) $this->lat, (float) $this->lng);

        return round($distance, 2);
    }

    private function resolveDeliveryFee(): ?float
    {
        if ($this->is_free_delivery) {
            return 0.0;
        }

        return $this->area?->base_fee;
    }

    private function calculateDistanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lng1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lng2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return $angle * $earthRadius;
    }
}
