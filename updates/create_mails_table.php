<?php namespace Pensoft\Mailing\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateMailsTable extends Migration
{
    public function up()
    {
        Schema::create('pensoft_mailing_mails', function (Blueprint $table) {
            $table->engine = 'InnoDB';
			$table->increments('id')->unsigned();
			$table->string('subject', 255);
			$table->text('body');
			$table->integer('from_user');
			$table->smallInteger('priority')->default(1);
			$table->timestamp('created_at')->nullable();
			$table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pensoft_mailing_mails');
    }
}
