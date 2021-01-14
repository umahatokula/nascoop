<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('pf')->unique()->nullable();
            $table->integer('ippis')->unique();
            $table->integer('center_id')->nullable();
            $table->integer('pay_point')->nullable();
            $table->string('sbu')->nullable();
            $table->string('title')->nullable();
            $table->string('fname')->nullable();
            $table->string('lname')->nullable();
            $table->string('full_name')->nullable();
            $table->string('slug'); 
            $table->string('email')->unique()->nullable();
            $table->string('username')->unique()->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('coop_no')->nullable();
            $table->string('nok_name')->nullable();
            $table->string('nok_phone')->nullable();
            $table->text('nok_address')->nullable();
            $table->string('nok_rship')->nullable();
            $table->integer('is_active')->unsigned()->default(1);
            $table->boolean('is_flagged')->default(0)->nullable();
            $table->text('flag-reason')->nullable();
            $table->boolean('is_approved')->default(0)->comment('Membership application approval');
            $table->dateTime('activation_date')->nullable();
            $table->dateTime('deactivation_date')->nullable();
            $table->double('shares_amount', 15,2)->default(0.00);
            $table->boolean('is_cooperative_member')->default(1);
            $table->boolean('savings_locked')->default(0)->comment('This value is zero if there is a pending trxn for a member');
            $table->string('primary_bank')->nullable();
            $table->string('primary_account_number')->nullable();
            $table->string('secondary_bank')->nullable();
            $table->string('secondary_account_number')->nullable();
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
        Schema::dropIfExists('members');
    }
}
