<?php

use Illuminate\Database\Migrations\Migration;

class CreateTableBilder extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('bilder', function($table) {

                    $table->engine = 'InnoDB';

                    $table->increments('id')->unsigned();
                    $table->integer('beitrag_id')->unsigned();
                    $table->string('filename')->length(120);
                    $table->string('localfilename')->length(40);
                    $table->string('mimetype')->length(20);
                    $table->text('bildunterschrift');
                    $table->integer('w')->unsigned();
                    $table->integer('h')->unsigned();
                    $table->integer('ww')->unsigned();
                    $table->integer('wh')->unsigned();
                    $table->integer('tw')->unsigned();
                    $table->integer('th')->unsigned();

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
        Schema::drop('bilder');
    }

}