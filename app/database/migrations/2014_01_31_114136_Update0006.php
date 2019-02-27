<?php

use Illuminate\Database\Migrations\Migration;

class Update0006 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('kategorien', function($table) {
            $table->text('eps_file')->length(80)->after('logo');
            $table->text('xml_tag')->length(80)->after('logo');
            $table->boolean('export_always')->boolean()->after('logo')->default(false);
            $table->boolean('no_headline')->boolean()->after('logo')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('kategorien', function($table) {
            $table->dropColumn('no_headline');
            $table->dropColumn('export_always');
            $table->dropColumn('xml_tag');
            $table->dropColumn('eps_file');
        });
    }

}
