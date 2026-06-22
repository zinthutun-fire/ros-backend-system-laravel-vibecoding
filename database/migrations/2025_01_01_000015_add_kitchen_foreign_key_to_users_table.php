<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('kitchen_id')
                ->references('id')
                ->on('kitchens')
                ->nullOnDelete();

            $table->index('kitchen_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['kitchen_id']);
            $table->dropIndex(['kitchen_id']);
        });
    }
};
