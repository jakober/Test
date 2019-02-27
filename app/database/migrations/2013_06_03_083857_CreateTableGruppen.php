<?php

use Illuminate\Database\Migrations\Migration;

class CreateTableGruppen extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('gruppen', function($table) {

                    $table->engine = 'InnoDB';

                    $table->increments('id')->unsigned();
                    $table->string('bezeichnung')->length(40);

                    $table->timestamps();
                });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('gruppen');
    }

}