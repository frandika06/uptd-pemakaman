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
        Schema::create('tpu_sarpras', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('uuid_lahan');
            $table->string('nama');
            $table->string('jenis_sarpras', 100)->nullable();
            $table->decimal('luas_m2', 10, 2)->nullable();
            $table->text('deskripsi')->nullable();

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
        Schema::dropIfExists('tpu_sarpras');
    }
};