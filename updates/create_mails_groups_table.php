<?php namespace Pensoft\Mailing\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateMailsGroupsTable extends Migration
{
	public function up(): void
	{
		if (!Schema::hasTable('pensoft_mailing_mails_groups')) {
			Schema::create('pensoft_mailing_mails_groups', function(Blueprint $table)
			{
				$table->engine = 'InnoDB';
				$table->integer('mails_id');
				$table->integer('groups_id');
				$table->primary(['mails_id', 'groups_id']);
			});
		}
	}

	public function down(): void
	{
		Schema::dropIfExists('pensoft_mailing_mails_groups');
	}
}