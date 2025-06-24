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
        Schema::create('portal_esertifikat', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->text('judul')->nullable();
            $table->text('slug')->nullable();
            $table->text('deskripsi')->nullable();
            $table->text('thumbnails')->nullable();
            $table->dateTime('tanggal')->nullable();
            $table->enum('tipe_publikasi', ["Public", "Private"])->default("Public");
            $table->string('password', 100)->nullable();
            $table->string('kategori')->nullable(); // 'Umum', 'Kegiatan', 'Webinar', 'Seminar', 'Event', 'Penghargaan'
            $table->enum('status', ['Draft', 'Pending Review', 'Published', 'Scheduled', 'Archived', 'Deleted'])->default('Draft');
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
        Schema::dropIfExists('portal_esertifikat');
    }
};