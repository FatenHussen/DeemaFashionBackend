<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        // load any files inside storage/app/public/payments
        $files = Storage::disk('public')->files('payments');

        $methods = [
            [
                'name'       => 'Cash',
                'code'       => 'cash',
                'icon'       => null,
                'is_active'  => true,
                'sort_order' => 1,
                'config'     => null,
            ],
            [
                'name'       => 'Syriatel',
                'code'       => 'syriatel',
                'icon'       => null,
                'is_active'  => true,
                'sort_order' => 2,
                'config'     => null,
            ],
            [
                'name'       => 'MTN Cash',
                'code'       => 'mtn_cash',
                'icon'       => null,
                'is_active'  => true,
                'sort_order' => 3,
                'config'     => null,
            ],
            [
                'name'       => 'Card (Stripe)',
                'code'       => 'stripe',
                'icon'       => null,
                'is_active'  => true,
                'sort_order' => 4,
                'config'     => null,
            ],
        ];

        // assign icons from the files list by index if available
        foreach ($methods as $i => &$m) {
            if (isset($files[$i])) {
                $m['icon'] = $files[$i];
            }
        }
        unset($m);

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['code' => $method['code']],
                $method
            );
        }
    }
}
