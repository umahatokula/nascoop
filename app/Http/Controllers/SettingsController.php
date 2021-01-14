<?php

namespace App\Http\Controllers;
use App\ProcessingFee;
use App\Bank;
use App\WithdrawalSetting;

use Illuminate\Http\Request;

class SettingsController extends Controller
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

    
    /**
     * Display form to save charges
     */
    public function charges() {
        $processing_fee = ProcessingFee::first();
        $banks = Bank::all();
        $withdrawalSetting = WithdrawalSetting::where('type', 'withdrawal_percentage_charge')->first();

        if (request()->ajax()) {
            return [
                'processing_fee' => $processing_fee,
                'banks' => $banks,
                'withdrawal_setting' => $withdrawalSetting,
            ];
        }

        $data['banks'] = $banks;

        return view('settings.charges', $data);
    }

    /**
     * Save bank charges
     */
    public function saveBankCharges(Request $request) {
        // dd( $request->all());

        $rules = [
            'name' => 'required',
            'transfer_charge' => 'required',

        ];

        $messages = [
            'name.required' => 'The bank name is required',
            'transfer_charge.required' => 'This transfer charge is required',
        ];

        $this->validate($request, $rules, $messages);

        $bank = new Bank;
        $bank->name = $request->name;
        $bank->code = $request->code;
        $bank->transfer_charge = $request->transfer_charge;
        $bank->done_by = auth()->user()->ippis;
        $bank->save();

        return ['banks' => Bank::all()];

        return $bank;
    }

    /**
     * Edit bank charges
     */
    public function editBankCharges(Request $request) {
        // dd( $request->all());

        $rules = [
            'name' => 'required',
            'transfer_charge' => 'required',

        ];

        $messages = [
            'name.required' => 'The bank name is required',
            'transfer_charge.required' => 'This transfer charge is required',
        ];

        $this->validate($request, $rules, $messages);

        $bank = Bank::find($request->id);
        $bank->name = $request->name;
        $bank->code = $request->code;
        $bank->transfer_charge = $request->transfer_charge;
        $bank->done_by = auth()->user()->ippis;
        $bank->save();

        return ['banks' => Bank::all()];

        return $bank;
    }

    /**
     * Delete a bank
     */
    public function deleteBank ($id) {
        $bank = Bank::find($id);

        if ($bank) {
            $bank->delete();
        }

        return ['banks' => Bank::all()];
    }

    /**
     * Edit withdrawal charge
     */
    public function editWithdrawalPercentageCharge(Request $request) {
        // dd( $request->all());

        $rules = [
            // 'name' => 'required',
            'value' => 'required',
        ];

        $messages = [
            // 'name.required' => 'The name is required',
            'value.required' => 'The percentage charge is required',
        ];

        $this->validate($request, $rules, $messages);

        $withdrawalSetting = WithdrawalSetting::where('type', 'withdrawal_percentage_charge')->first();
        $withdrawalSetting->name = $request->name;
        $withdrawalSetting->type = 'withdrawal_percentage_charge';
        $withdrawalSetting->value = $request->value;
        $withdrawalSetting->done_by = auth()->user()->ippis;
        $withdrawalSetting->save();

        return ['withdrawal_setting' => $withdrawalSetting];
    }

    /**
     * Edit processing fee
     */
    public function editProcessingFee(Request $request) {
        // dd( $request->all());

        $rules = [
            'amount' => 'required',

        ];

        $messages = [
            'amount.required' => 'The amount is required',
        ];

        $this->validate($request, $rules, $messages);

        $processing_fee = ProcessingFee::first();
        $processing_fee->amount = $request->amount;
        $processing_fee->done_by = auth()->user()->ippis;
        $processing_fee->save();

        return ['processing_fee' => $processing_fee];
    }
}
