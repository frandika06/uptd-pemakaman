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
        Schema::create('portal_faq_list', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('uuid_portal_faq')->nullable();
            $table->integer('no_urut')->default(1);
            $table->text('pertanyaan')->nullable();
            $table->longText('jawaban')->nullable();
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
        Schema::dropIfExists('portal_faq_list');
    }
};