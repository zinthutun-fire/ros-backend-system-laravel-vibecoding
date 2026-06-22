<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_item_id')->constrained();
            $table->foreignId('kitchen_id')->constrained();
            $table->integer('qty');
            $table->decimal('price', 10, 2);
            $table->decimal('subtotal', 12, 2);
            $table->text('note')->nullable();
            $table->string('status')->default('pending');
            $table->text('void_reason')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['order_id', 'kitchen_id', 'status']);
            $table->index('kitchen_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
