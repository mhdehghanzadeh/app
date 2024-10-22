<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('unit_number')->unique()->nullable();
            $table->string('first_name', 20)->nullable();
            $table->string('last_name', 30)->nullable();
            $table->string('national_code', 10)->unique()->nullable();
            $table->date('birthday')->nullable();
            $table->boolean('sex')->nullable();
            $table->string('state', 50)->nullable();
            $table->string('insurance', 20)->nullable();
            $table->string('supplementary_insurance', 20)->nullable();
            $table->string('photo', 100)->nullable();
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
        Schema::dropIfExists('contacts');
    }
}
