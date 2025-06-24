<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('apps_counter', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('nama_apps', 100)->nullable(); // Portal SMA, dll
            $table->enum('visual_template', ['FE', 'BE'])->nullable();
            $table->bigInteger('views')->default(1);
            $table->enum('device', ['mobile', 'web'])->nullable();
            $table->date('tanggal')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apps_counter');
    }
};
