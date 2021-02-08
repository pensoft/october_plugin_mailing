<?php namespace Pensoft\Mailing\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateMailsUsersTable extends Migration
{
	public function up()
	{
		if (!Schema::hasTable('pensoft_mailing_mails_users')) {
			Schema::create('pensoft_mailing_mails_users', function(Blueprint $table)
			{
				$table->engine = 'InnoDB';
				$table->integer('mails_id');
				$table->integer('user_id');
				$table->primary(['mails_id', 'user_id']);
			});
		}
	}

	public function down()
	{
		Schema::dropIfExists('pensoft_mailing_mails_users');
	}
}

