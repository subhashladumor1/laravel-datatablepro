<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('table_presets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('table_key');
            $table->string('preset_name');
            $table->json('columns')->nullable();
            $table->json('filters')->nullable();
            $table->integer('page_length')->default(10);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'table_key']);
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_presets');
    }
};
