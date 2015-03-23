<?php

use Illuminate\Database\Migrations\Migration;

class CreateRegistryTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Config::get('registry.table'), function($table)
        {
            $table->string('key');
            $table->text('value');

            $table->primary('key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop(Config::get('registry.table'));
    }

}