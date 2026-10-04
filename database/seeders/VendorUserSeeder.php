<?php

namespace Database\Seeders;

use App\Models\Vendor;
use App\Models\VendorUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VendorUserSeeder extends Seeder
{
    public function run(): void
    {
        $vendor = Vendor::first();

        if (! $vendor) {
            return;
        }

        VendorUser::create([
            'name' => 'Deema Fashion Admin',
            'email' => 'admin@deemafashion.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'vendor_id' => $vendor->id,
        ]);
    }
}
