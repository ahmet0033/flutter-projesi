<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('store_name')->default('RedEast');
            $table->char('currency', 3)->default('USD');
            $table->decimal('tax_rate', 5, 2)->default(20.00);
            $table->boolean('tax_inclusive')->default(true);
            $table->string('order_prefix', 12)->default('RE');
            $table->string('support_email')->nullable();
            $table->string('support_phone')->nullable();
            $table->string('address')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('x_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
