<?php namespace Pensoft\Mailing\Updates;

use Schema;
use Illuminate\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateGroupsTable extends Migration
{
	public function up(): void
	{
		if (!Schema::hasTable('pensoft_mailing_groups')) {
			Schema::create('pensoft_mailing_groups', function(Blueprint $table)
			{
				$table->engine = 'InnoDB';
				$table->increments('id')->unsigned();
				$table->string('name', 255);
				$table->string('email', 255);
				$table->timestamp('created_at')->nullable();
				$table->timestamp('updated_at')->nullable();
				$table->smallInteger('type')->default(1);
			});
		}
	}

    public function down(): void
    {
        Schema::dropIfExists('pensoft_mailing_groups');
    }
}