<?php

use Illuminate\Database\Migrations\Migration;

class CreateTableTextvorlagen extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('textvorlagen', function($table) {
                    $table->engine = 'InnoDB';

                    $table->increments('id')->unsigned();
                    $table->integer('kategorie_id')->unsigned()->nullable();
                    $table->integer('user_id')->unsigned();
                    $table->string('name')->length(80)->nullable();
                    $table->string('ueberschrift')->length(80)->nullable();
                    $table->string('untertitel')->length(80)->nullable();
                    $table->text('text')->nullable();

                    $table->timestamps();

                    $table->foreign('kategorie_id')->references('id')->on('kategorien');
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('textvorlagen');
    }

}