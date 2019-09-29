<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageTestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_a', function (Blueprint $table) {
            $table->increments('id');
            $table->text('col_1');

            $table->timestamps();
        });

        Schema::create('table_b', function (Blueprint $table) {
            $table->increments('id');
            $table->text('col_1')->default(base64_encode('col_1_value'));

            $table->timestamps();
        });

        Schema::create('table_c', function (Blueprint $table) {
            $table->increments('id');
            $table->text('col_1')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_a');
        Schema::dropIfExists('table_b');
        Schema::dropIfExists('table_c');
    }
}