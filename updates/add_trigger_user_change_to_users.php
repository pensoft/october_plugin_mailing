<?php namespace Pensoft\Mailing\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class AddTriggerUserChangeToUsers extends Migration
{
	public function up(): void
	{
		if (Schema::hasTable('users') && !Schema::hasColumn('users', 'trigger_backend_save')) {
			Schema::table('users', function (Blueprint $table) {
				$table->smallInteger('trigger_backend_save')->default(0);;
			});
		}
	}

	public function down(): void
	{
		if (Schema::hasColumn('users', 'trigger_backend_save')) {
			Schema::table('users', function (Blueprint $table) {
				$table->dropColumn('trigger_backend_save');
			});
		}
	}
}