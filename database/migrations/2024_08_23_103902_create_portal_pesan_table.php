<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('portal_pesan', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('nama_lengkap', 100)->nullable();
            $table->string('no_telp', 100)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('instansi', 100)->nullable();
            $table->text('subjek')->nullable();
            $table->text('pesan')->nullable();
            $table->text('balasan')->nullable();
            $table->enum('status', ['Pending', 'Responded'])->default('Pending');
            $table->uuid('uuid_created')->nullable();
            $table->uuid('uuid_updated')->nullable();
            $table->uuid('uuid_deleted')->nullable();
            $table->timestamps();
            $table->softdeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_pesan');
    }
};