<?php namespace Pensoft\Mailing\Updatess;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddUserIdToGroups extends Migration
{
	public function up()
	{
		if (!Schema::hasColumn('pensoft_mailing_groups', 'user_id')) {
			Schema::table('pensoft_mailing_groups', function($table)
			{
				$table->integer('user_id')->nullable();
			});
		}
	}

	public function down()
	{
		if (Schema::hasColumn('pensoft_mailing_groups', 'user_id')) {
			Schema::table('pensoft_mailing_groups', function($table)
			{
				$table->dropColumn('user_id');
			});
		}
	}
}
