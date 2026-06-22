<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_table_id')->constrained('tables');
            $table->foreignId('to_table_id')->constrained('tables');
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();

            $table->index('from_table_id');
            $table->index('to_table_id');
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_transfers');
    }
};
