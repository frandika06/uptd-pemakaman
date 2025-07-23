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
        Schema::create('tpu_petugas', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('uuid_user')->nullable();
            $table->uuid('uuid_tpu')->nullable();
            $table->string('nip', 50)->nullable();
            $table->string('nama_lengkap', 255)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('kontak', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->text('foto')->nullable();

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
        Schema::dropIfExists('tpu_petugas');
    }
};