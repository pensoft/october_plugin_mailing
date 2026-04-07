<?php namespace Pensoft\Mailing\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class AddUserIdToGroups extends Migration
{
	public function up(): void
	{
		if (!Schema::hasColumn('pensoft_mailing_groups', 'user_id')) {
			Schema::table('pensoft_mailing_groups', function(Blueprint $table)
			{
				$table->integer('user_id')->nullable();
			});
		}
	}

	public function down(): void
	{
		if (Schema::hasColumn('pensoft_mailing_groups', 'user_id')) {
			Schema::table('pensoft_mailing_groups', function(Blueprint $table)
			{
				$table->dropColumn('user_id');
			});
		}
	}
}