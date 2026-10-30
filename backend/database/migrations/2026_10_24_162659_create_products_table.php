<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price_cents');
            $table->unsignedBigInteger('compare_at_price_cents')->nullable();
            $table->char('currency', 3)->default('USD');
            $table->string('image_path')->nullable();
            $table->string('image_url')->nullable();
            $table->json('specs')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'is_active']);
            $table->index(['brand_id', 'is_active']);
            $table->index('price_cents');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
