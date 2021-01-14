<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIppisDeductionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ippis_deductions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('deduction_for');
            $table->integer('center_id');
            $table->string('day');
            $table->string('month');
            $table->string('year');
            $table->integer('done_by')->nullable();
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
        Schema::dropIfExists('ippis_deductions');
    }
}
