<?php namespace Pensoft\Mailing\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateGroupsUsersTable extends Migration
{
	public function up()
	{
		if (!Schema::hasTable('pensoft_mailing_groups_users')) {
			Schema::create('pensoft_mailing_groups_users', function(Blueprint $table)
			{
				$table->engine = 'InnoDB';
				$table->integer('group_id');
				$table->integer('user_id');
				$table->primary(['group_id', 'user_id']);
			});
		}
	}

	public function down()
	{
		Schema::dropIfExists('pensoft_mailing_groups_users');
	}
}

