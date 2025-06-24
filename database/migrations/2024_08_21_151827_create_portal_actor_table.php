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
        Schema::create('portal_actor', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('uuid_user')->unique()->nullable();
            $table->string('nip', 50)->nullable();
            $table->string('nama_lengkap', 255)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('kontak', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->text('foto')->nullable();
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
        Schema::dropIfExists('portal_actor');
    }
};
