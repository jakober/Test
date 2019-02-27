<?php

use Illuminate\Database\Migrations\Migration;

class CreateTableKeywords extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('keywords', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('mandant_id')->unsigned();
            $table->string('keyword')->length(80);
            $table->timestamps();

            $table->unique(array('mandant_id', 'keyword'));
            $table->foreign('mandant_id')->references('id')->on('mandanten');

        });

        Schema::create('kategorien_keywords', function($table) {
                    $table->engine = 'InnoDB';
                    $table->increments('id');
                    $table->integer('kategorie_id')->unsigned();
                    $table->integer('keyword_id')->unsigned();
                    $table->timestamps();

                    $table->unique(array('kategorie_id', 'keyword_id'));

                    $table->foreign('kategorie_id')->references('id')->on('kategorien');
                    $table->foreign('keyword_id')->references('id')->on('keywords');
                });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('kategorien_keywords');
        Schema::drop('keywords');
    }


    }