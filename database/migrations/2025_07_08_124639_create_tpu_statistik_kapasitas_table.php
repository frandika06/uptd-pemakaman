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
        Schema::create('tpu_statistik_kapasitas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('uuid_tpu');
            $table->date('bulan');
            $table->integer('total_lahan')->default(0);
            $table->integer('total_kapasitas')->default(0);
            $table->integer('sisa_kapasitas')->default(0);

            $table->string('uuid_created', 100)->nullable();
            $table->string('uuid_updated', 100)->nullable();
            $table->string('uuid_deleted', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tpu_statistik_kapasitas');
    }
};