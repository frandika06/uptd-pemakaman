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
        Schema::create('tpu_kategori_dokumens', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('nama')->nullable();
            $table->string('tipe', 100)->nullable(); // foto, dokumen-tpu, dokumen-iptm
            $table->enum('status', ['0', '1'])->default("1");

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
        Schema::dropIfExists('tpu_kategori_dokumens');
    }
};
