<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMonthlySavingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monthly_savings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ref')->nullable();
            $table->date('payment_date')->nullable();
            $table->integer('ippis');
            $table->foreign('ippis')->references('ippis')->on('members')->onUpdate('cascade')->onDelete('cascade');
            $table->double('amount', 15,2)->nullable();
            $table->double('old_amount', 15,2)->nullable();
            $table->double('new_amount', 15,2)->nullable();
            $table->boolean('is_indefinite')->default(0)->nullable();
            $table->date('revert_date')->nullable();
            $table->integer('applied_by')->nullable();
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
        Schema::dropIfExists('monthly_savings');
    }
}
