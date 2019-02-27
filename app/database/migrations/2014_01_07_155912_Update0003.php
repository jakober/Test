<?php

use Illuminate\Database\Migrations\Migration;

class Update0003 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('users', function($table) {
            $table->dropIndex('users_mandant_id_username_unique');
            //$table->dropColumn('username');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('users', function($table) {
            $table->unique(array('mandant_id', 'username'));
        });
    }

}
