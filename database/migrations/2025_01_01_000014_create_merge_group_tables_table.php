<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merge_group_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_merge_id')->constrained('table_merges')->cascadeOnDelete();
            $table->foreignId('table_id')->constrained('tables');
            $table->timestamps();

            $table->unique(['table_merge_id', 'table_id']);
            $table->index('table_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merge_group_tables');
    }
};
