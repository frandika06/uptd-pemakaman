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
        Schema::create('portal_kategori', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('nama', 100)->nullable();
            $table->string('slug', 200)->nullable();
            $table->string('type', 200)->nullable(); // 'Post', 'Infografis', dll
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
        Schema::dropIfExists('portal_kategori');
    }
};