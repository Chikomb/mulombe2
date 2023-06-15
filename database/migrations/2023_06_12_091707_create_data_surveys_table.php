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
            $table->string('session_id');
            $table->string('phone_number');
            $table->string('telecom_operator');
            $table->string('channel');
            $table->string('question_number');
            $table->text('question');
            $table->string('answer');
            $table->string('answer_value');
            $table->string('data_category');
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
