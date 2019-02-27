<?php

use Illuminate\Database\Migrations\Migration;

class CreateTableBeitraege extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('beitraege', function($table) {
                    $table->engine = 'InnoDB';
                    $table->increments('id')->unsigned();
                    $table->integer('mandant_id')->unsigned();
                    $table->integer('kategorie_id')->unsigned()->nullable();
                    $table->integer('user_id')->unsigned();
                    $table->string('ueberschrift')->nullable();
                    $table->string('untertitel')->nullable();
                    $table->text('text')->nullable();
                    $table->integer('status_id')->nullable();
                    $table->text('kommentar');
                    $table->boolean('kommentar_rathaus');
                    $table->boolean('kommentar_redaktion');
                    $table->timestamps();

                    $table->foreign('status_id')->references('id')->on('status');
                    $table->foreign('mandant_id')->references('id')->on('mandanten');
                    $table->foreign('kategorie_id')->references('id')->on('kategorien');
                    $table->foreign('user_id')->references('id')->on('users');
                });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('beitraege');
    }

}