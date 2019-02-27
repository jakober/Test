<?php

use Illuminate\Database\Migrations\Migration;

class Update0004 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('ausgaben', function($table) {
            $table->integer('export_revision')->after('erscheint');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('ausgaben', function($table) {
            $table->dropColumn('export_revision');
        });
    }

}
