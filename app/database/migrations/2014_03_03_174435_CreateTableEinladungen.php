<?php

use Illuminate\Database\Migrations\Migration;

class CreateTableEinladungen extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('einladungen', function($table) {

            $table->engine = 'InnoDB';

            $table->increments('id')->unsigned();
            $table->string('anrede')->length(1);
            $table->string('name')->length(120);
            $table->string('email')->length(120);
            $table->integer('mandant_id')->unsigned();

            $table->timestamps();
            $table->foreign('mandant_id')->references('id')->on('mandanten');

            $table->unique(array('mandant_id', 'email'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('einladungen');
    }

}
