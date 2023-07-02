<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('language_id')->nullable();
            $table->string("phone_number")->nullable();
            $table->string("case_no")->nullable();
            $table->string("step_no")->nullable();
            $table->string("session_id")->nullable();
            $table->boolean("status")->default(0);
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
        Schema::dropIfExists('sms_sessions');
    }
}
