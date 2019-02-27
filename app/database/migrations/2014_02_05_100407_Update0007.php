<?php

use Illuminate\Database\Migrations\Migration;

class Update0007 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('eps', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('mandant_id')->unsigned();
            $table->string('filename')->length(80);
            $table->timestamps();
        });

        Schema::table('kategorien', function($table) {
            $table->dropColumn('eps_file')->default(null);
            $table->integer('eps_id')->after('xml_tag')->unsigned()->nullable();
            $table->foreign('eps_id')->references('id')->on('eps'); // funktioniert nicht wegen null
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('kategorien', function($table) {
            $table->dropColumn('eps_id');
            $table->string('eps_file')->length(80)->after('xml_tag');
        });

        Schema::drop('eps');
    }

}
