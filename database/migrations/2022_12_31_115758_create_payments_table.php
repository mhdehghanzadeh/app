<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('contact_id')->unsigned();
            $table->integer('consultant_id')->unsigned();
            $table->integer('counseling_id')->unsigned()->nullable();
            $table->string('transaction_id');
            $table->integer('amount');
            $table->string('reference_id')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->boolean('result')->default(0);
            $table->boolean('verify')->default(0);
            $table->string('driver')->nullable();
            $table->timestamps();
            $table->foreign('contact_id')->references('id')->on('contacts');
            $table->foreign('consultant_id')->references('id')->on('consultants');
            $table->foreign('counseling_id')->references('id')->on('counselings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
