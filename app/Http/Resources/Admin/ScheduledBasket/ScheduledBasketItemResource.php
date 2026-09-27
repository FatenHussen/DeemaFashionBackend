<?php

namespace App\Http\Resources\Admin\ScheduledBasket;

use App\Http\Resources\Basket\BasketItemProductResource;
use App\Http\Resources\Basket\BasketItemVariantResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduledBasketItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'variant_id' => $this->variant_id,
            'shop_product_variant_id' => $this->shop_product_variant_id,
            'variant_sku' => $this->variant?->sku,
            'variant_model' => $this->variant?->model,
            'variant_barcode' => $this->variant?->barcode,
            'brand' => $this->product?->brand ? [
                'id' => $this->product->brand->id,
                'name' => $this->product->brand->name,
                'image' => $this->product->brand->image_url ?? null,
            ] : null,

            // Alternatives (simplified list for user selection)


            // Product details
            'product' => $this->product ? new BasketItemProductResource($this->product) : null,

            // Variant details
            'variant' => $this->variant ? new BasketItemVariantResource($this->variant) : null,

            // Shop variants (detailed list including primary + alternatives)
            // 'shop_variants' => $this->getShopVariants(),
            'alternatives' => $this->getAlternatives(),
            // Quantities and pricing
            'quantity' => (int) $this->quantity,
            'unit_price' => round($this->price, 2),
            'subtotal' => $this->subtotal,

            // Scheduled basket specific settings
            'is_required' => (bool) $this->is_required,
            'is_extra' => (bool) $this->is_extra,
            'min_quantity' => (int) $this->min_quantity,
            'max_quantity' => (int) $this->max_quantity,

            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get alternatives (simplified list for user selection)
     */
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

        return $variants->map(function ($variant) {
            $product = optional($variant->productVariant)->product;
            $brand = optional($product)->brand;

            return [
                'product_id' => $product->id ?? null,
                'shop_product_variant_id' => $variant->id,
                'shop_id' => $variant->shop_id,
                'is_restaurant' => (bool) ($variant->shop?->is_restaurant ?? false),
                'city_id' => $variant->shop?->city_id ?? $variant->shop?->area?->city_id,
                'name' => trim(($product->name ?? '') . ' ' . ($brand->name ?? '')),
                'brand' => $brand ? [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'image' => $brand->image_url ?? null,
                ] : null,
                'image_url' => $product?->image_url,
                'price' => (float) $variant->productVariant?->price,
                'discount' => (float) $variant->productVariant?->discount,
                'price_after_discount' => (float) $variant->productVariant?->price_after_discount,
                'sku' => $variant->productVariant?->sku,
                'model' => $variant->productVariant?->model,
                'barcode' => $variant->productVariant?->barcode,
            ];
        })->values();
    }

    /**
     * Get shop variants (detailed list including primary + alternatives)
     */
    private function getShopVariants()
    {
        // Collect all variant IDs (primary + alternatives)
        $variantIds = [];

        if ($this->shop_product_variant_id) {
            $variantIds[] = $this->shop_product_variant_id;
        }

        if (!empty($this->shop_product_variant_ids)) {
            $variantIds = array_merge($variantIds, $this->shop_product_variant_ids);
        }

        // Remove duplicates
        $variantIds = array_unique($variantIds);

        if (empty($variantIds)) {
            return [];
        }

        $variants = \App\Models\ShopProductVariant::query()
            ->whereIn('id', $variantIds)
            ->with(['shop.area', 'productVariant.product'])
            ->get();

        return $variants->map(function ($variant) {
            return [
                'id' => $variant->id,
                'shop_id' => $variant->shop_id,
                'shop_name' => $variant->shop?->name,
                'is_restaurant' => (bool) ($variant->shop?->is_restaurant ?? false),
                'city_id' => $variant->shop?->city_id ?? $variant->shop?->area?->city_id,
                'product_name' => $variant->productVariant?->product?->name,
                'price' => $variant->productVariant?->price,
                'discount' => $variant->productVariant?->discount,
                'price_after_discount' => $variant->productVariant?->price_after_discount,
                'sku' => $variant->productVariant?->sku,
                'model' => $variant->productVariant?->model,
                'barcode' => $variant->productVariant?->barcode,
                'quantity' => $variant->productVariant?->quantity,
            ];
        })->values();
    }
}
