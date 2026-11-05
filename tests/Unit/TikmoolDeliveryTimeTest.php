<?php

namespace Tests\Unit;

use App\Models\Product;
use Tests\TestCase;

class TikmoolDeliveryTimeTest extends TestCase
{
    public function test_tikmool_delivery_time_follows_the_request_locale(): void
    {
        $product = new Product(['vendor_id' => 1]);

        app()->setLocale('en');
        $this->assertSame('12-48 hours', $product->effective_delivery_time);

        app()->setLocale('ar');
        $this->assertSame("\u{2066}12-48\u{2069} ساعة", $product->effective_delivery_time);
    }

    public function test_seller_delivery_time_is_left_as_entered(): void
    {
        $product = new Product([
            'vendor_id' => 8,
            'delivery_time' => '3-5 أيام',
        ]);

        app()->setLocale('en');
        $this->assertSame('3-5 أيام', $product->effective_delivery_time);
    }
}
