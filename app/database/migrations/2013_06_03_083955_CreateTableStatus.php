<?php

use Illuminate\Database\Migrations\Migration;

class CreateTableStatus extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('status', function($table) {
                    $table->engine = 'InnoDB';
                    $table->integer('id');
                    $table->primary('id');
                    $table->string('bezeichnung');
                    $table->string('bild')->length(20)->nullable()->default(null);
                    $table->timestamps();
                });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('status');
    }

}