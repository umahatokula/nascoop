<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Ledger_Internal;
use App\AccountType;

class AccountController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function listAccounts() {

        $accounts = Ledger_Internal::all();

        dd($this->accountsTree($accounts));

    }

    public function accountsTree($accounts) {

        // $tree = [];

        foreach ($accounts as $account) {
            $children = $account->getChildren();

            if ($children->count() > 0) {
                $tree[] = [$account->account_name, $children];
                $this->accountsTree($children);
            } else {
                $parent = $accounts->where('id', $account->parent_id)->first();

                $tree[] = [$parent, $account->account_name];
            } 
        }

        return $tree;
    }

}
