<?php

use Illuminate\Database\Migrations\Migration;

class CreateTableAktivierungsschluessel extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('aktivierungsschluessel', function($table) {
                    $table->engine = 'InnoDB';

                    $table->integer('id')->primary();
                    $table->string('key');
                    $table->timestamps();
                    // TODO foreign key geht nicht!?
                    //$table->foreign('user_id')->references('id')->on('users');

                });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('aktivierungsschluessel');
    }

}