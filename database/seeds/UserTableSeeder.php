<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Member;
use App\Account;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $member = new Member;
        $member->ippis = '17679';
        $member->username = '17679';
        $member->full_name = 'John Okpanachi';
        $member->pay_point = 7;
        $member->save();

        // open an account for this member
        Account::insert([
            'account_no' => $member->ippis,
            'entity_type' => 'p',
        ]);

        $user = User::create([
            'name'      => $member->full_name, 
            'username'  => $member->ippis, 
            'ippis'     => $member->ippis, 
            'password'  => \Hash::make('17679'), 
        ]);

        // assign role
        $user->assignRole('member');
        $user->assignRole('secretary');
        $user->assignRole('auditor');
        $user->assignRole('accountant');
    }
}
