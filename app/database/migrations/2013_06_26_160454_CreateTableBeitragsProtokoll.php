<?php

use Illuminate\Database\Migrations\Migration;

class CreateTableBeitragsProtokoll extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('beitragsprotokoll', function($table) {
                    $table->engine = 'InnoDB';

                    $table->increments('id')->unsigned();
                    $table->integer('beitrag_id')->unsigned();
                    $table->integer('recipient_user_id')->unsigned()->nullable();
                    $table->integer('action_user_id')->unsigned();
                    $table->boolean('fuer_rathaus');
                    $table->boolean('fuer_redaktion');
                    $table->integer('action_id')->nullable()->default(null);

                    $table->timestamps();

                    $table->foreign('beitrag_id')->references('id')->on('beitraege')->onDelete('cascade');
                    $table->foreign('recipient_user_id')->references('id')->on('users')->onDelete('cascade');
                    $table->foreign('action_user_id')->references('id')->on('users')->onDelete('cascade');
                });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('beitragsprotokoll');
    }

}