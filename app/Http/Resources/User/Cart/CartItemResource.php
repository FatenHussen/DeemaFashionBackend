<?php

namespace App\Http\Resources\User\Cart;

use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray($request): array
    {
        $shopVariant = $this->shopProductVariant;
        $productVariant = $shopVariant?->productVariant;
        $product = $productVariant?->product;
        $shop = $shopVariant?->shop;
        $quantity = (int) $this->quantity;
        $unitPrice = (float) ($productVariant?->final_price ?? 0);
        $lineTotal = $unitPrice * $quantity;

        $variantLabels = [];
        if ($productVariant) {
            $resolved = $productVariant->getAttributesValuesAttribute()->resolve();
            $variantLabels = collect($resolved)
                ->map(function (array $row) {
                    $attribute = $row['attribute'] ?? null;
                    $value = $row['value'] ?? null;
                    if ($attribute && $value) {
                        return "{$attribute}: {$value}";
                    }

                    return $value;
                })
                ->filter()
                ->values()
                ->all();
        }

        return [
            'id' => $this->id,
            'shop_product_variant_id' => $this->shop_product_variant_id,
            'shop_id' => $shopVariant?->shop_id,
            'shop' => $shop ? [
                'id' => $shop->id,
                'name' => $shop->name,
            ] : null,
            'quantity' => $quantity,
            'note' => $this->note,
            'product' => $product ? [
                'id' => $product->id,
                'name' => $product->name,
                'image' => $product->image_url,
            ] : null,
            'sku' => $productVariant?->sku,
            'stock_quantity' => $productVariant?->quantity,
            'variant' => $variantLabels,
            'unit_price' => $this->convertPrice($unitPrice),
            'line_total' => $this->convertPrice($lineTotal),
        ];
    }
}
