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
        Schema::create('portal_duta_sma', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->text('judul')->nullable();
            $table->text('slug')->nullable();
            $table->text('thumbnails')->nullable();
            $table->string('nama_peserta_1')->nullable();
            $table->string('nama_peserta_2')->nullable();
            $table->string('nama_sekolah_1')->nullable();
            $table->string('nama_sekolah_2')->nullable();
            $table->string('predikat_1')->nullable();
            $table->string('predikat_2')->nullable();
            $table->longText('deskripsi_1')->nullable();
            $table->longText('deskripsi_2')->nullable();
            $table->text('avatar_1')->nullable();
            $table->text('avatar_2')->nullable();
            $table->text('link_ig_1')->nullable();
            $table->text('link_ig_2')->nullable();
            $table->text('link_fb_1')->nullable();
            $table->text('link_fb_2')->nullable();
            $table->text('link_tiktok_1')->nullable();
            $table->text('link_tiktok_2')->nullable();
            $table->text('link_twitter_1')->nullable();
            $table->text('link_twitter_2')->nullable();
            $table->text('link_youtube_1')->nullable();
            $table->text('link_youtube_2')->nullable();
            $table->text('link_linkedin_1')->nullable();
            $table->text('link_linkedin_2')->nullable();
            $table->year('tahun')->nullable();
            $table->integer('views')->default("0");
            $table->string('kategori')->nullable();
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
        Schema::dropIfExists('portal_duta_sma');
    }
};
