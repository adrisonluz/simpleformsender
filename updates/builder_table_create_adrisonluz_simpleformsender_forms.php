<?php namespace AdrisonLuz\SimpleFormSender\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAdrisonluzSimpleformsenderForms extends Migration
{
    public function up()
    {
        Schema::create('adrisonluz_simpleformsender_forms', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('label', 255);
            $table->string('type', 255);
            $table->string('mailto', 255)->nullable();
            $table->text('obs')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('adrisonluz_simpleformsender_forms');
    }
}
