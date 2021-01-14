<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLedgerSnapShotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ledger_snap_shots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('exports')->nullable();
            $table->string('pay_point_id');
            $table->string('deduction_for')->nullable();
            $table->boolean('is_done')->default(0);
            $table->integer('done_by')->default(0);
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
        Schema::dropIfExists('ledger_snap_shots');
    }
}
