<?php

use Illuminate\Database\Migrations\Migration;

class CreateTableUsers extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('users', function($table) {
            $table->engine = 'InnoDB';

            $table->increments('id')->unsigned();
            $table->integer('mandant_id')->unsigned();
            $table->integer('gruppe_id')->unsigned()->default(1);
            $table->string('username')->length(32);
            $table->string('password')->length(60);

            $table->string('anrede')->length(1);
            $table->string('name')->length(40);
            $table->string('vorname')->length(40);
            $table->string('firma')->length(80);
            $table->string('strasse')->length(80);
            $table->string('plz')->length(5);
            $table->string('ort')->length(60);
            $table->string('telefon')->length(40);
            $table->string('mobilnummer')->length(40);
            $table->string('fax')->length(40);
            $table->string('email')->length(80);

            $table->boolean('aktiviert');
            $table->boolean('freigeschaltet')->boolean()->default(false);

            $table->timestamps();

            $table->unique(array('mandant_id', 'username'));
            $table->unique(array('mandant_id', 'email'));

            $table->foreign('mandant_id')->references('id')->on('mandanten');
            $table->foreign('gruppe_id')->references('id')->on('gruppen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('users');
    }

}
