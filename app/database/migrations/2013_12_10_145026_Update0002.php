<?php

use Illuminate\Database\Migrations\Migration;

class Update0002 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('beitraege', function($table) {
            //// 1
            $table->dropForeign('beitraege_user_id_foreign');
        });

        // 2
        DB::update('ALTER TABLE `beitraege` CHANGE COLUMN `user_id` `user_id` INT(10) UNSIGNED NULL');

        Schema::table('beitraege', function($table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('SET NULL');
        });

        Schema::table('user_kategorien', function($table) {
            $table->dropForeign('user_kategorien_user_id_foreign');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
        });
}



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('beitraege', function($table) {
            //$table->dropIndex('beitraege_user_id_foreign'); // Anstatt dropForeign
            // 1
            //$table->foreign('user_id')->references('id')->on('users');
        });
    }

}
