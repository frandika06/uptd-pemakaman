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
        Schema::create('portal_banner', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->text('judul')->nullable();
            $table->text('deskripsi')->nullable();
            $table->dateTime('tanggal')->nullable();
            $table->string('warna_text', 100)->nullable();
            $table->text('url')->nullable();
            $table->text('thumbnails')->nullable();
            $table->string('kategori')->nullable(); // 'Hero', 'Header', 'Event', 'Content', 'Footer'
            $table->enum('status', ["0", "1"])->default("1");
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
        Schema::dropIfExists('portal_banner');
    }
};