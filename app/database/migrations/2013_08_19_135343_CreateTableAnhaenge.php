<?php

use Illuminate\Database\Migrations\Migration;

class CreateTableAnhaenge extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('anhaenge', function($table) {

                    $table->engine = 'InnoDB';

                    $table->increments('id')->unsigned();
                    $table->integer('beitrag_id')->unsigned();
                    $table->string('filename')->length(120);
                    $table->string('mimetype')->length(40);
                    $table->integer('size');
                    $table->timestamps();

                    $table->foreign('beitrag_id')->references('id')->on('beitraege')->onDelete('cascade');
                });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('anhaenge');
    }

}