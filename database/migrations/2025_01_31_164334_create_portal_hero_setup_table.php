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
        Schema::create('portal_hero_setup', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('nama_pengaturan', 255)->unique();
            $table->string('heading_1', 255)->nullable();
            $table->string('heading_2', 255)->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('judul_tombol_aksi', 255)->nullable();
            $table->text('url_tombol_aksi')->nullable();
            $table->string('judul_tombol_video', 255)->nullable();
            $table->text('url_tombol_video')->nullable();
            $table->text('background_gambar')->nullable();
            $table->text('background_video')->nullable();
            $table->text('illustration')->nullable();
            $table->string('uuid_created', 100)->nullable();
            $table->string('uuid_updated', 100)->nullable();
            $table->string('uuid_deleted', 100)->nullable();
            $table->timestamps();
            $table->softdeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_hero_setup');
    }
};