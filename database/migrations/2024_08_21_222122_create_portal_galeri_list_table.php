<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('portal_galeri_list', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('uuid_galeri', 100)->nullable();
            $table->integer('no_urut')->default(1);
            $table->text('judul')->nullable();
            $table->text('url')->nullable();
            $table->string('tipe', 100)->nullable();
            $table->integer('size')->default("0");
            $table->integer('downloads')->default("0");
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
        Schema::dropIfExists('portal_galeri_list');
    }
};