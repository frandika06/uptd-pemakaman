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
        Schema::create('portal_links', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->integer('no_urut')->default(1);
            $table->text('judul')->nullable();
            $table->text('url')->nullable();
            $table->string('kategori')->nullable();
            $table->enum('status', ['0', '1'])->default("1");
            $table->string('uuid_created', 100)->nullable();
            $table->string('uuid_updated', 100)->nullable();
            $table->string('uuid_deleted', 100)->nullable();
            $table->timestamps();
            $table->softdeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_links');
    }
};
