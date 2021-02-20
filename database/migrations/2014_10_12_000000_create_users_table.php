<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('email')->unique()->nullable();
                $table->foreign('email')->references('email')->on('members')->onUpdate('cascade')->onDelete('cascade');
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->integer('ippis')->unique();
                // $table->foreign('ippis')->references('ippis')->on('members')->onUpdate('cascade')->onDelete('cascade');
                $table->integer('is_active')->default(1);
                $table->rememberToken();
                $table->timestamps();
                
                $table->string('activation_code')->nullable()->index();
                $table->string('persist_code')->nullable();
                $table->string('reset_password_code')->nullable()->index();
                $table->boolean('is_activated')->default(1);
                $table->timestamp('activated_at')->nullable();
                $table->timestamp('last_login')->nullable();

                $table->timestamp('deleted_at')->nullable();
                $table->boolean('is_guest')->default(false);
                $table->string('created_ip_address')->nullable();
                $table->string('last_ip_address')->nullable();
                $table->timestamp('last_seen')->nullable();
                $table->string('username')->unique()->nullable()->index();
                // $table->foreign('username')->references('username')->on('members')->onUpdate('cascade')->onDelete('cascade');
                $table->boolean('is_superuser')->default(false);
                $table->string('surname')->nullable();
            });
        } else {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'id')) {
                    $table->bigIncrements('id');
                }
                if (!Schema::hasColumn('users', 'name')) {
                    $table->string('name');
                }

                if (!Schema::hasColumn('users', 'email')) {
                    $table->string('email')->unique()->nullable();
                } else {
                    $table->string('email')->nullable()->change();
                }

                if (!Schema::hasColumn('users', 'email_verified_at')) {
                    $table->timestamp('email_verified_at')->nullable();
                }
                if (!Schema::hasColumn('users', 'password')) {
                    $table->string('password');
                }
                if (!Schema::hasColumn('users', 'ippis')) {
                    $table->integer('ippis')->unique();
                }
                if (!Schema::hasColumn('users', 'is_active')) {
                    $table->boolean('is_active')->default(1);
                }
                if (!Schema::hasColumn('users', 'remember_token')) {
                    $table->rememberToken();
                }
                if (!Schema::hasColumn('users', 'created_at') && !Schema::hasColumn('users', 'updated_at')) {
                    $table->timestamps();
                }



                if (!Schema::hasColumn('users', 'activation_code')) {
                    $table->string('activation_code')->nullable()->index();
                }

                if (!Schema::hasColumn('users', 'persist_code')) {
                    $table->string('persist_code')->nullable();
                }

                if (!Schema::hasColumn('users', 'reset_password_code')) {
                    $table->string('reset_password_code')->nullable()->index();
                }

                if (Schema::hasColumn('users', 'permissions')) {
                    $table->dropColumn('permissions');
                }
                
                if (!Schema::hasColumn('users', 'is_activated')) {
                    $table->boolean('is_activated')->default(1);
                }
                
                if (!Schema::hasColumn('users', 'activated_at')) {
                    $table->timestamp('activated_at')->nullable();
                }
                
                if (!Schema::hasColumn('users', 'last_login')) {
                    $table->timestamp('last_login')->nullable();
                }
                
                if (!Schema::hasColumn('users', 'deleted_at')) {
                    $table->timestamp('deleted_at')->nullable();
                }
                
                if (!Schema::hasColumn('users', 'is_guest')) {
                    $table->boolean('is_guest')->default(false);
                }
                
                if (!Schema::hasColumn('users', 'created_ip_address')) {
                $table->string('created_ip_address')->nullable();
                }
                
                if (!Schema::hasColumn('users', 'last_ip_address')) {
                    $table->string('last_ip_address')->nullable();
                }
                
                if (!Schema::hasColumn('users', 'last_seen')) {
                    $table->timestamp('last_seen')->nullable();
                }
                
                if (!Schema::hasColumn('users', 'username')) {
                    $table->string('username')->unique()->nullable()->index();
                }
                
                if (!Schema::hasColumn('users', 'is_superuser')) {
                    $table->boolean('is_superuser')->default(false);
                }
                
                if (!Schema::hasColumn('users', 'surname')) {
                    $table->string('surname')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
