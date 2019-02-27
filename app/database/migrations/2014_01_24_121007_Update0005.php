<?php

use Illuminate\Database\Migrations\Migration;

class Update0005 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('users', function($table) {
            $table->boolean('direktfreigabe')->after('email')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('ausgaben', function($table) {
            $table->dropColumn('direktfreigabe');
        });
    }

}