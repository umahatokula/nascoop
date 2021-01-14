<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIppisReconciledDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ippis_reconciled_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('ippis_deductions_import_id');
            $table->boolean('is_successful')->default(0)->comment('Indicate if deduction was successful');
            $table->boolean('is_reconciled')->default(0)->comment('Indicate if reconociliation was done');
            $table->string('month');
            $table->string('year');
            $table->integer('ippis');
            $table->string('name')->nullable();
            $table->string('pay_point')->nullable();
            $table->string('expected_savings')->nullable();
            $table->string('remitted_savings')->nullable();
            $table->string('expected_ltl')->nullable();
            $table->string('remitted_ltl')->nullable();
            $table->string('expected_stl')->nullable();
            $table->string('remitted_stl')->nullable();
            $table->string('expected_coml')->nullable();
            $table->string('remitted_coml')->nullable();
            $table->string('message')->nullable();
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
        Schema::dropIfExists('ippis_reconciled_data');
    }
}
