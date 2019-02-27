<?php

use Illuminate\Database\Migrations\Migration;

class CreateTableKategorien extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('kategorien', function($table) {
                    $table->engine = 'InnoDB';

                    $table->increments('id')->unsigned();
                    $table->integer('mandant_id')->unsigned();
                    $table->string('bezeichnung')->length(80);
                    $table->integer('hauptkategorie')->unsigned()->nullable();
                    $table->integer('sortierung')->unsigned();
                    $table->string('logo')->length(80);
                    $table->boolean('has_nodes')->default(false);
                    $table->integer('tiefe')->unsigned()->default(0);
                    $table->integer('reihenfolge')->unsigned();

                    $table->timestamps();

                    $table->index(array('mandant_id','reihenfolge'));
                    $table->foreign('mandant_id')->references('id')->on('mandanten');
                    $table->foreign('hauptkategorie')->references('id')->on('kategorien');
                });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('kategorien');
    }

}