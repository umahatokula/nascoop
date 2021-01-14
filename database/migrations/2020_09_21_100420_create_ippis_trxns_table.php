<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIppisTrxnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ippis_trxns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('trxn_number')->nullable();
            $table->boolean('is_authorized')->default(0);
            $table->integer('center_id')->nullable();
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();
            $table->date('deduction_for')->nullable();
            $table->double('ms_dr', 15,2)->default(0.00);
            $table->double('ms_cr', 15,2)->default(0.00);
            $table->double('ms_bal', 15,2)->default(0.00);
            $table->double('ltl_dr', 15,2)->default(0.00);
            $table->double('ltl_cr', 15,2)->default(0.00);
            $table->double('ltl_bal', 15,2)->default(0.00);
            $table->double('stl_dr', 15,2)->default(0.00);
            $table->double('stl_cr', 15,2)->default(0.00);
            $table->double('stl_bal', 15,2)->default(0.00);
            $table->double('coml_dr', 15,2)->default(0.00);
            $table->double('coml_cr', 15,2)->default(0.00);
            $table->double('coml_bal', 15,2)->default(0.00);
            $table->integer('done_by')->nullable();
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
        Schema::dropIfExists('ippis_trxns');
    }
}
