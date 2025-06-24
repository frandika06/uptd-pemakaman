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
        Schema::create('portal_esertifikat_list', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('uuid_esertifikat')->nullable();
            $table->integer('no_urut')->default(1);
            $table->text('nama_lengkap')->nullable();
            $table->text('instansi')->nullable();
            $table->text('url')->nullable();
            $table->string('tipe', 100)->nullable();
            $table->integer('size')->default("0");
            $table->integer('views')->default("0");
            $table->integer('downloads')->default("0");
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
        Schema::dropIfExists('portal_esertifikat_list');
    }
};
