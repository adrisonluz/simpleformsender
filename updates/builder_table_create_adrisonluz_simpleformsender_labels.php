<?php namespace AdrisonLuz\SimpleFormSender\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAdrisonluzSimpleformsenderLabels extends Migration
{
    public function up()
    {
        Schema::create('adrisonluz_simpleformsender_labels', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 255);
            $table->string('label', 255);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('adrisonluz_simpleformsender_labels');
    }
}
