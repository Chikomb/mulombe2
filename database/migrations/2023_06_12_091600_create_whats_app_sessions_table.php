<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhatsAppSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whats_app_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('language_id');
            $table->string("phone_number");
            $table->string("case_no");
            $table->string("step_no");
            $table->string("session_id");
            $table->boolean("status");
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
        Schema::dropIfExists('whats_app_sessions');
    }
}
