<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageTestTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_a', function (Blueprint $tableRow) {
            $tableRow->increments('id');
            $tableRow->text('col_1');

            $tableRow->timestamps();
        });

        Schema::create('table_b', function (Blueprint $tableRow) {
            $tableRow->increments('id');
            $tableRow->text('col_1')->default(base64_encode('col_1_value'));

            $tableRow->timestamps();
        });

        Schema::create('table_c', function (Blueprint $tableRow) {
            $tableRow->increments('id');
            $tableRow->text('col_1')->nullable();

            $tableRow->timestamps();
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