<?php

use Illuminate\Database\Migrations\Migration;

class CreateTableAusgabenBeitraege extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('ausgaben_beitraege', function($table) {
                    $table->engine = 'InnoDB';
                    $table->increments('id');
                    $table->integer('ausgabe_id')->unsigned();
                    $table->integer('beitrag_id')->unsigned();
                    $table->boolean('exportiert')->default(false);
                    $table->timestamps();

                    $table->unique(array('ausgabe_id', 'beitrag_id'));

                    $table->foreign('ausgabe_id')->references('id')->on('ausgaben')->onDelete('cascade');;
                    $table->foreign('beitrag_id')->references('id')->on('beitraege')->onDelete('cascade');;
                });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('ausgaben_beitraege');
    }

}