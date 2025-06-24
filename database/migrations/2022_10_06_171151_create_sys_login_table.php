<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSysLoginTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_login', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid_profile')->nullable();
            $table->string('status', 100)->nullable();
            $table->string('ip', 100)->nullable();
            $table->string('agent', 255)->nullable();
            $table->string('device', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sys_login');
    }
}
