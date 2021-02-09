<?php namespace Pensoft\Mailing\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateAliasesTable extends Migration
{
    public function up()
    {
        Schema::create('pensoft_mailing_aliases', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pensoft_mailing_aliases');
    }
}
