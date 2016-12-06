<?php namespace AdrisonLuz\SimpleFormSender\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAdrisonluzSimpleformsenderFormsRegister extends Migration
{
    public function up()
    {
        Schema::create('adrisonluz_simpleformsender_forms_register', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('type', 255);
            $table->string('label', 255);
            $table->string('table_form', 255);
            $table->string('model', 255);
        });
    }

    public function down()
    {
        Schema::dropIfExists('adrisonluz_simpleformsender_forms_register');
    }
}
