<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Cities are created by GovernorateSeeder (Canada provinces → cities → areas).
 * Kept for backward compatibility; do not call this seeder independently.
 */
class CitySeeder extends Seeder
{
    public function run(): void
    {
        // No-op: geography hierarchy is seeded via GovernorateSeeder.
    }
}
