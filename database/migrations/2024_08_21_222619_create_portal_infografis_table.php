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
        Schema::create('portal_infografis', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->text('judul')->nullable();
            $table->text('slug')->nullable();
            $table->text('deskripsi')->nullable();
            $table->longText('post')->nullable();
            $table->dateTime('tanggal')->nullable();
            $table->text('url')->nullable();
            $table->string('tipe', 100)->nullable();
            $table->integer('size')->default("0");
            $table->integer('views')->default("0");
            $table->integer('downloads')->default("0");
            $table->string('kategori')->nullable();
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
        Schema::dropIfExists('portal_infografis');
    }
};