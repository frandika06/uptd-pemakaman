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
        Schema::create('portal_data_direktur', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->integer('no_urut')->default(1);
            $table->string('nama_lengkap')->nullable();
            $table->text('foto')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('masa_jabatan')->nullable();
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
        Schema::dropIfExists('portal_data_direktur');
    }
};