<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConsultantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consultants', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('name', 100);
            $table->text('about')->nullable();
            $table->string('specialty')->nullable();
            $table->integer('npcode')->nullable();
            $table->string('adress')->nullable();
            $table->string('location')->nullable();
            $table->string('telephone')->nullable();
            $table->string('time')->nullable();
            $table->integer('price')->nullable();
            $table->string('photo', 100)->nullable();
            $table->boolean('active');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('consultants');
    }
}
