<?php namespace Pensoft\Mailing\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdatePensoftMailingGroups extends Migration
{
    public function up()
    {
        Schema::table('pensoft_mailing_groups', function($table)
        {
            $table->string('replace_from')->nullable();
            $table->string('replace_to')->nullable();
            $table->string('add_reply_to')->nullable();
            $table->string('name_append')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('pensoft_mailing_groups', function($table)
        {
            $table->dropColumn('replace_from');
            $table->dropColumn('replace_to');
            $table->dropColumn('add_reply_to');
            $table->dropColumn('name_append');
        });
    }
}
