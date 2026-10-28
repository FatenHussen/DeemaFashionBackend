<?php

use App\Support\CatalogShadeColors;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        CatalogShadeColors::link();
    }

    public function down(): void
    {
        CatalogShadeColors::unlink();
    }
};
