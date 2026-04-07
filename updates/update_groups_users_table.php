<?php namespace Pensoft\Mailing\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class UpdateGroupsUsersTable extends Migration
{
	public function up(): void
	{
		Schema::table('pensoft_mailing_groups_users', function(Blueprint $table)
		{
			$table->renameColumn('group_id', 'groups_id');
		});
	}

	public function down(): void
	{
		Schema::table('pensoft_mailing_groups_users', function(Blueprint $table)
		{
			$table->renameColumn('groups_id', 'group_id');
		});
	}
}