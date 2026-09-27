<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Address\AllResource as AddressOneResource;
use App\Http\Resources\Basket\AllResource as BasketAllResource;
use App\Http\Resources\BasketSchedule\AllResource as BasketScheduleAllResource;
use App\Http\Resources\Driver\AllResource as DriverAllResource;
use App\Http\Resources\EndUser\AllResource;
use App\Http\Resources\SectionItem\AllResource as SectionItemAllResource;
use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverOneResource extends JsonResource
{
    use HasCurrencyConversion;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $groupedItems = $this->items
            ->groupBy(function ($item) {
                return $item->shopProductVariant->shop_id;
            })
            ->map(function ($items) {
                $shop = $items->first()->shopProductVariant->shop;

                return [
                    'id' => $shop->id,
                    'shop' => $shop->name,
                    'lat' => $shop->lat,
                    'lng' => $shop->lng,
                    'items' => OrderItemResource::collection($items),
                ];
            })
            ->values(); // مهم لإعادة ترتيب المفاتيح (0,1,2...)
        return [
            'id' => $this->id,
            'order_code' => $this->order_code,
            'status' => $this->status,
            'cart_type' => $this->cart_type,
            'is_instant_delivery' => $this->is_instant_delivery,
            'scheduled_delivery_at' => $this->scheduled_delivery_at?->format('Y-m-d H:i'),
            ...$this->withCurrency($this->delivery_price, 'delivery_price'),
            ...$this->withCurrency($this->subtotal, 'subtotal'),
            ...$this->withCurrency($this->total, 'total'),
            'total_quantity' => $this->total_quantity,

            'assigned_by' => $this->assigned_by,



            'created_at' => $this->created_at?->toDateTimeString(),

            'user' => AllResource::make($this->user),
            'driver' => DriverAllResource::make($this->driver),
            'user_address' => AddressOneResource::make($this->address),
            'payment_method_id' => $this->payment_method_id,
            'is_paid' => (bool) $this->is_paid,
            'payment_method' => $this->paymentMethod ? [
                'id' => $this->paymentMethod->id,
                'name' => $this->paymentMethod->name,
                'icon' => $this->paymentMethod->icon,

            ] : null,
            // 'baskes' => $this->basket ? BasketAllResource::make($this->basket) : null,
            // 'basket_schedule' => $this->basket_schedule_id ? BasketScheduleAllResource::make($this->basketSchedule) : null,
            'shops' => $groupedItems
        ];
    }
}
