<?php

use Illuminate\Database\Migrations\Migration;

class CreateTableUserKategorien extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('user_kategorien', function($table) {
                    $table->engine = 'InnoDB';
                    $table->increments('id');
                    $table->integer('user_id')->unsigned();
                    $table->integer('kategorie_id')->unsigned();
                    $table->boolean('aktiv')->default(false);

                    $table->timestamps();

                    $table->unique(array('user_id', 'kategorie_id'));

                    $table->foreign('user_id')->references('id')->on('users');
                    $table->foreign('kategorie_id')->references('id')->on('kategorien');
                });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('user_kategorien');
    }

}