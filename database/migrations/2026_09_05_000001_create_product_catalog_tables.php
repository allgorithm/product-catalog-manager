<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('sku')->unique();
            $table->string('name');
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->unsignedInteger('base_price_cents');
            $table->string('currency', 3)->default('EUR');
            $table->string('status', 20)->default('draft')->index();
            $table->timestamps();

            $table->index(['status', 'category_id', 'created_at']);
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->string('size');
            $table->string('color_name');
            $table->string('color_hex', 7)->nullable();
            $table->unsignedInteger('price_cents')->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->timestamps();

            $table->unique(['product_id', 'size', 'color_name']);
        });

        Schema::create('product_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('url');
            $table->string('alt_text')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_media');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
