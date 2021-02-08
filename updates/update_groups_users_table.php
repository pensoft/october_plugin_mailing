<?php namespace Pensoft\Mailing\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UpdateGroupsUsersTable extends Migration
{
	public function up()
	{
		Schema::table('pensoft_mailing_groups_users', function($table)
		{
			$table->renameColumn('group_id', 'groups_id');
		});
	}

	public function down()
	{
		Schema::table('pensoft_mailing_groups_users', function($table)
		{
			$table->renameColumn('groups_id', 'group_id');
		});
	}
}
