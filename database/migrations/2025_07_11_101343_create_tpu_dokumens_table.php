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
        Schema::create('tpu_dokumens', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('uuid_modul')->nullable();   // relasi ke tpu_datas / tpu_lahans / tpu_sarpras
            $table->string('nama_modul')->nullable(); // TPU / Lahan / Sarpras - PERBAIKAN: string bukan uuid
            $table->uuid('kategori');                 // relasi ke tpu_kategori_dokumens

            $table->string('nama_file');             // nama bebas yang ditampilkan
            $table->text('deskripsi')->nullable();   // deskripsi isi file
            $table->text('url')->nullable();         // path file
            $table->string('tipe', 100)->nullable(); // tipe file (ekstensi)
            $table->integer('size')->default("0");   // ukuran file

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
        Schema::dropIfExists('tpu_dokumens');
    }
};
