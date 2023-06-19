<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataSurveysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_surveys', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('telecom_operator')->nullable();
            $table->string('channel')->nullable();
            $table->unsignedBigInteger('language_id')->default(1);
            $table->string('question_number')->nullable();
            $table->text('question')->nullable();
            $table->string('answer')->nullable();
            $table->string('answer_value')->nullable();
            $table->string('data_category')->nullable();
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
        Schema::dropIfExists('data_surveys');
    }
}
