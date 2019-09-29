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
        Schema::create('images', function (Blueprint $table) {
            $table->increments('id');
            $table->text('image')->nullable(); // Nullable custom cast field
            $table->text('thumb')->default('thumb_placeholder.png'); // Custom cast field with default value
            $table->text('data')->default('[]');

            $table->timestamps();
        });

        Schema::create('data', function (Blueprint $table) {
            $table->increments('id');
            $table->text('field_1');
            $table->text('field_2')->nullable();

            $table->timestamps();
        });

//        Schema::create('data_1', function (Blueprint $table) {
//            $table->increments('id');
//            $table->text('field_1')->default(['test']);
//
//            $table->timestamps();
//        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data');
    }
}