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
        Schema::create('tpu_makams', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('uuid_lahan');
            $table->decimal('panjang_m', 5, 2);
            $table->decimal('lebar_m', 5, 2);
            $table->decimal('luas_m2', 10, 2)->nullable();
            $table->integer('kapasitas')->nullable();
            $table->string('status_makam', 100)->nullable();
            $table->text('keterangan')->nullable();

            $table->string('uuid_created', 100)->nullable();
            $table->string('uuid_updated', 100)->nullable();
            $table->string('uuid_deleted', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tpu_makams');
    }
};
