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
        Schema::create('portal_tanos', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('uuid_kategori')->nullable();
            $table->uuid('uuid_kategori_sub')->nullable();
            $table->integer('no_urut')->default(1);
            $table->text('judul')->nullable();
            $table->text('slug')->nullable();
            $table->text('deskripsi')->nullable();
            $table->text('thumbnails')->nullable();
            $table->string('nama_group')->nullable();
            $table->string('tanggal')->nullable();
            $table->string('sumber')->nullable(); // Link, Upload
            $table->text('url')->nullable();
            $table->string('tipe', 100)->nullable();
            $table->integer('size')->default("0");
            $table->integer('views')->default("0");
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
        Schema::dropIfExists('portal_tanos');
    }
};
