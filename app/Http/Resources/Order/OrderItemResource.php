<?php

namespace App\Http\Resources\Order;

use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray(Request $request): array
    {
        $shopVariant = $this->shopProductVariant;
        $productVariant = $shopVariant?->productVariant;
        $product = $productVariant?->product;
        $shop = $shopVariant?->shop;

        $shopName = $shop?->name;
        if (is_array($shopName)) {
            $locale = app()->getLocale();
            $shopName = $shopName[$locale]
                ?? $shopName['ar']
                ?? $shopName['en']
                ?? collect($shopName)->filter()->first();
        }

        $sku = $productVariant?->sku ?: $product?->sku;
        $modelNumber = $productVariant?->model ?: $product?->model;
        $availableQuantity = $productVariant?->quantity;
        if ($availableQuantity === null && $product !== null) {
            $availableQuantity = $product->quantity;
        }

        return [
            'id' => $this->id,
            'product_id' => $product?->id,
            'product_name' => $this->product_name,
            'product_image' => $this->product_image,
            'shop_name' => $shopName,
            'sku' => $sku,
            'model_number' => $modelNumber,
            'model' => $modelNumber,
            'available_quantity' => $availableQuantity,
            'stock' => $availableQuantity,
            'is_external' => (bool) $this->is_external,
            'invoice_image' => $this->invoice_image_url,
            'shop_product_variant_id' => $this->shop_product_variant_id,
            'note' => $this->note,
            'quantity' => $this->quantity,
            ...$this->withCurrency($this->unit_price, 'unit_price'),
            ...$this->withCurrency($this->final_price, 'final_price'),
            ...$this->withCurrency($this->subtotal, 'subtotal'),
            ...$this->withCurrency($this->extras_total, 'extras_total'),
            ...$this->withCurrency($this->total, 'total'),
            'status' => $this->item_status,
            'variant_attributes' => $this->variant_attributes,
            'delivery_time' => $this->getDeliveryTime(),
            'extras' => $this->extras->map(function ($extra) {
                return [
                    'id' => $extra->extraDetail->id,
                    'detail_key' => $extra->extraDetail->detail_key,
                    'detail_value' => $extra->extraDetail->detail_value,
                    'price' => $extra->price,
                    'quantity' => $extra->quantity,
                    'price_currencies' => $this->dualCurrency($extra->price),
                ] ?? [];
            }),
        ];
    }

    private function getDeliveryTime(): ?string
    {
        $product = $this->shopProductVariant?->productVariant?->product;

        if (!$product) {
            return null;
        }

        return $product->effective_delivery_time;
    }
}
