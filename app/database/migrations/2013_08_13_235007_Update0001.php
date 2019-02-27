<?php

use Illuminate\Database\Migrations\Migration;

class Update0001 extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::table('beitragsprotokoll',function($table){
                    $table->text('nachricht')->nullable()->after('action_id');
                });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
                Schema::table('beitragsprotokoll',function($table){
                    $table->dropColumn('nachricht');
                });
	}

}