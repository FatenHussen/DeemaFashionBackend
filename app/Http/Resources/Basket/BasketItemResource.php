<?php

namespace App\Http\Resources\Basket;

use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Resources\Json\JsonResource;

class BasketItemResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray($request)
    {
        $user = auth('user')->user();
        $currencyId = $user?->currency_id;

        return [
            'id' => $this->id,
            'quantity' => (int) $this->quantity,
            ...$this->withCurrency($this->price, 'unit_price'),
            ...$this->withCurrency($this->subtotal, 'subtotal'),
            'is_required' => $this->is_required,
            'is_extra' => $this->is_extra,
            'min_quantity' => (int) $this->min_quantity,
            'max_quantity' => (int) $this->max_quantity,
            'can_adjust' => $this->canAdjustQuantity(),
            'shop_product_variant_id' => $this->shop_product_variant_id,
            'variant_sku' => $this->variant?->sku,
            'variant_model' => $this->variant?->model,
            'variant_barcode' => $this->variant?->barcode,
            'brand' => $this->product?->brand ? [
                'id' => $this->product->brand->id,
                'name' => $this->product->brand->name,
                'image' => $this->product->brand->image_url ?? null,
            ] : null,
            'product' =>new BasketItemProductResource($this->whenLoaded('product')) ?? null,

            'variant' => new BasketItemVariantResource($this->whenLoaded('variant')) ?? null,
            'alternatives' => $this->getAlternatives(),

            // 'companies' => BasketItemCompanyResource::collection($this->whenLoaded('companies')) ??  [],
        ];
    }
    private function getAlternatives()
    {
        if (empty($this->shop_product_variant_ids)) {
            return [];
        }

        $variants = \App\Models\ShopProductVariant::query()
            ->whereIn('id', $this->shop_product_variant_ids)
            ->with([
                'shop.area',
                'productVariant.product.brand',
                'productVariant.product.media'
            ])
            ->get();

        $user = auth('user')->user();
        $currencyId = $user?->currency_id;

        return $variants->map(function ($variant) use ($currencyId) {

            $product = optional($variant->productVariant)->product;
            $brand   = optional($product)->brand;

            $productVariant = $variant->productVariant;

            $priceData = $this->convertPrice($productVariant?->price, $currencyId);

            return [
                'product_id' => $product->id ?? null,
                'shop_product_variant_id' => $variant->id,
                'shop_id' => $variant->shop_id,
                'is_restaurant' => (bool) ($variant->shop?->is_restaurant ?? false),
                'city_id' => $variant->shop?->city_id ?? $variant->shop?->area?->city_id,

                'name' => trim(
                    ($product->name ?? '') . ' ' . ($brand->name ?? '')
                ),
                'brand' => $brand ? [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'image' => $brand->image_url ?? null,
                ] : null,

                'image_url' => $product?->image_url,

                'price' => $priceData['amount'],
                'price_formatted' => $priceData['formatted'],
                'currency' => $priceData['currency'],
                'currency_symbol' => $priceData['symbol'],
                'price_currencies' => $this->dualCurrency($productVariant?->price),
                'discount' => $productVariant?->discount,
                'discount_currencies' => $this->dualCurrency($productVariant?->discount),
                'price_after_discount' => $productVariant?->price_after_discount,
                'price_after_discount_currencies' => $this->dualCurrency($productVariant?->price_after_discount),
                'sku' => $productVariant?->sku,
                'model' => $productVariant?->model,
                'barcode' => $productVariant?->barcode,
            ];
        })->values();
    }

}
