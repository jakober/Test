<?php

use Illuminate\Database\Migrations\Migration;

class CreateTableAusgaben extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('ausgaben', function($table) {
                    $table->engine = 'InnoDB';
                    $table->increments('id')->unsigned;
                    $table->integer('mandant_id')->unsigned();
                    $table->integer('kw')->unsigned();
                    $table->integer('jahr')->unsigned();
                    $table->dateTime('redschl');
                    $table->date('erschdat');
                    $table->boolean('erscheint')->default(false);
                    $table->timestamps();

                    $table->foreign('mandant_id')->references('id')->on('mandanten');
                });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('ausgaben');
    }

}