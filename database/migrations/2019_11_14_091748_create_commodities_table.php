<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommoditiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commodities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('inventory_item_id')->comment('inventory item_id')->nullable();
            $table->double('unit_price', 15,2)->comment('inventory item unit price')->nullable();
            $table->integer('quantity')->comment('inventory item qty')->nullable();
            $table->integer('pay_point')->comment('members pay point');
            $table->string('ref')->nullable();
            $table->date('loan_date')->nullable();
            $table->date('loan_end_date')->nullable();
            $table->integer('ippis');
            $table->foreign('ippis')->references('ippis')->on('members')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('no_of_months')->nullable();
            $table->double('total_amount', 15,2)->nullable();
            $table->double('monthly_amount', 15,2)->nullable();
            $table->double('adjustment', 15,2)->nullable();
            $table->double('processing_fee', 15,2)->nullable();
            $table->integer('bank_id')->nullable();
            $table->double('bank_charges', 15,2)->nullable();
            $table->double('interest', 15,2)->nullable();
            $table->double('net_payment', 15,2)->nullable();
            $table->string('interest_percentage')->nullable();
            $table->string('pv_number')->nullable();
            $table->integer('guarantor_1')->nullable();
            $table->boolean('guarantor_1_approved')->default(0)->nullable();
            $table->integer('guarantor_1_approved_amount')->nullable();
            $table->integer('guarantor_2')->nullable();
            $table->boolean('guarantor_2_approved')->default(0)->nullable();
            $table->integer('guarantor_2_approved_amount')->nullable();
            $table->string('disapproval_reason')->nullable()->comment('Reason coop staff disapprooved this loan');
            $table->integer('applied_by')->nullable();
            $table->integer('done_by')->nullable();
            $table->boolean('start_processing')->default(0)->nullable();
            $table->boolean('is_approved')->default(0)->nullable();
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
        Schema::dropIfExists('commodities');
    }
}
