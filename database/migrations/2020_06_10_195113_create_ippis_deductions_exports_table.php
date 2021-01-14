<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIppisDeductionsExportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ippis_deductions_exports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->json('exports')->nullable();
            $table->string('deduction_for');
            $table->string('center_id')->nullable();
            $table->boolean('is_done')->default(0);
            $table->integer('done_by');
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
        Schema::dropIfExists('ippis_deductions_exports');
    }
}
