<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatchedErrorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catched_errors', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('type');
            $table->string('exception');
            $table->text('message');
            $table->string('file');
            $table->string('url');
            $table->integer('line');
            $table->boolean('reportar')->default(true);
            $table->json('trace');
            $table->json('request');
            $table->json('occurrences');
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
        Schema::dropIfExists('catched_errors');
    }
}
