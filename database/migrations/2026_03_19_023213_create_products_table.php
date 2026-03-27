<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('category_id')->constrained('product_categories')->onDelete('cascade');
        $table->string('title');
        $table->text('description');
        $table->integer('price');
        $table->float('rating')->default(0);
        $table->string('thumbnail')->nullable();
        $table->string('file_path');
        $table->integer('download_count')->default(0);
        $table->enum('status', ['active', 'inactive'])->default('active');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
