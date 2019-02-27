<?php

use Illuminate\Database\Migrations\Migration;

class CreateTableSessions extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create(Config::get('session.table'), function($table) {
                    $table->engine = 'InnoDB';
                    $table->string('id')->length(40)->primary('session_primary');
                    $table->integer('last_activity');
                    $table->text('payload');
                });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop(Config::get('session.table'));
    }

}