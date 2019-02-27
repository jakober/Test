<?php

use Illuminate\Database\Migrations\Migration;

class CreateTableMandanten extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('mandanten', function($table) {
                    $table->engine = 'InnoDB';

                    $table->increments('id')->unsigned();
                    $table->string('bezeichnung')->length(80);
                    $table->string('logo')->length(80);
                    $table->string('email_verwaltung')->length(80)->default('info@bairle.de');
                    $table->string('name_verwaltung')->length(80)->default('Redaktionssystem');
                    $table->string('hostname');
                    $table->text('email_footer');
                    $table->timestamps();
                });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('mandanten');
    }

}