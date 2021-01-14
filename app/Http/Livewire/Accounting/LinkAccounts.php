<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use App\Center;
use App\Ledger_Internal;
use App\TransactionType_Ext;

class LinkAccounts extends Component
{
	public $centers;
	public $accounts = [];
	public $trxn_types;
	public $dr = null;
	public $cr = null;

	protected $rules = [
        'dr' => 'required',
        'cr' => 'required',
    ];

	protected $messages = [
        'dr.required' => 'Select a DR account for the transaction type',
        'cr.required' => 'Select a CR account for the transaction type',
    ];

	protected $listeners = ['accountLinked'];

    public function mount() {

        $this->trxn_types = TransactionType_Ext::all();
        $this->centers = Center::all();

        $all_account = Ledger_Internal::all();
        foreach($all_account as $account) {
            $this->accounts[] = [
                'account_name' => $account->account_name .' - '.$account->ledger_no,
                'ledger_no' => $account->ledger_no
            ];
        }
    }

    public function render()
    {
        return view('livewire.accounting.link-accounts');
    }

    public function accountLinked($xact_type_code_ext) {

    	$this->validate();

    	$trxn_type = TransactionType_Ext::where('xact_type_code_ext', $xact_type_code_ext)->first();
    	$trxn_type->update(['associated_trxns->dr' => $this->dr, 'associated_trxns->cr' => $this->cr]);

    	$this->reset(['dr', 'cr']);

    	session()->flash('message', $trxn_type->description.' successfully linked.');
    }

}
