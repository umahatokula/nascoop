<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInitialImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('initial_imports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('imports');
            $table->string('pay_point');
            $table->string('bank')->nullable();
            $table->string('upload_date');
            $table->boolean('is_done')->default(0);
            $table->softDeletes();
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
        Schema::dropIfExists('initial_imports');
    }
}
