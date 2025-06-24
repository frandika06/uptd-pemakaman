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
        Schema::create('portal_setup', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('nama_pengaturan', 100)->nullable();
            $table->longText('value_pengaturan')->nullable();
            $table->string('kategori', 100)->nullable(); // 'Menus', dll
            $table->string('sites', 100)->nullable();    // 'Portal', 'Tanos'
            $table->enum('status', ['0', '1'])->default("1");
            $table->uuid('uuid_created')->nullable();
            $table->uuid('uuid_updated')->nullable();
            $table->uuid('uuid_deleted')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_setup');
    }
};