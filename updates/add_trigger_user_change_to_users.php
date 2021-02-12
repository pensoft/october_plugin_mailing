<?php namespace Pensoft\Mailing\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddTriggerUserChangeToUsers extends Migration
{
	public function up()
	{
		if (!Schema::hasColumn('users', 'trigger_backend_save')) {
			Schema::table('users', function ($table) {
				$table->smallInteger('trigger_backend_save')->default(0);;
			});
		}
	}

	public function down()
	{
		if (Schema::hasColumn('users', 'trigger_backend_save')) {
			Schema::table('users', function ($table) {
				$table->dropColumn('trigger_backend_save');
			});
		}
	}
}
