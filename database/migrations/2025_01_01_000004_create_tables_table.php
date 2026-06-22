<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('table_no')->unique();
            $table->string('name')->nullable();
            $table->integer('capacity')->default(4);
            $table->foreignId('area_id')->constrained('table_areas');
            $table->string('status')->default('available');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('status');
            $table->index('area_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
