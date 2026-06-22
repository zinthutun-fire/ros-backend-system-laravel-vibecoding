<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kitchen_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->boolean('has_modifiers')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('status')->default('available');
            $table->timestamps();

            $table->index('category_id');
            $table->index('kitchen_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
