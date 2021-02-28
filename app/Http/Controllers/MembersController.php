<?php

namespace App\Http\Controllers;

use App\Member;
use App\User;
use App\Account;
use App\MonthlySaving;
use App\MonthlySavingsPayment;
use App\LongTerm;
use App\LongTermLoanDefault;
use App\LongTermPayment;
use App\ShortTerm;
use App\ShortTermLoanDefault;
use App\ShortTermPayment;
use App\Commodity;
use App\CommodityLoanDefault;
use App\CommodityPayment;
use App\Ledger;
use App\Center;
use App\ActivityLog;
use App\ProcessingFee;
use App\Bank;
use App\Ledger_Internal;
use App\WithdrawalSetting;
use Carbon\Carbon;
use\Toastr;
use Importer;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MembersRegisterExport;
use Exporter;

use Illuminate\Http\Request;
use LaravelDaily\LaravelCharts\Classes\LaravelChart;

class MembersController extends Controller
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
     * Display a listing of all members
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        // dd($request->all());
        $selectedStatus = null;

        $membersQuery = Member::query();

        if ($request->pay_point) {
            $membersQuery = $membersQuery->where('pay_point', $request->pay_point);
        }
        // dd($request->status);

        if ($request->status == 'active') {
            $selectedStatus = $request->status;
            $membersQuery = $membersQuery->where('is_active', 1);
        } 
        if ($request->status == 'deactivated') {
            $selectedStatus = $request->status;
            $membersQuery = $membersQuery->where('is_active', 0);
        }
        

        $data['members'] = $membersQuery->paginate(20);
        $data['centers'] = Center::pluck('name', 'id');
        $data['selectedStatus'] = $selectedStatus;

        return view('members.index', $data);
    }

    /**
     * Show a members details
     */
    public function show($ippis) {


        $data['member'] = Member::where('ippis', $ippis)->first();

        return view('members.show', $data);
    }

    /**
     * Display a member's dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard($ippis) {
        $data['member'] = Member::where('ippis', $ippis)->first();
        $chart_options = [
            'chart_title' => 'Transactions by dates',
            'report_type' => 'group_by_date',
            'model' => 'App\Ledger',
            'group_by_field' => 'created_at',
            'group_by_period' => 'day',
            'aggregate_function' => 'sum',
            'aggregate_field' => 'savings_bal',
            'chart_type' => 'line',
        ];
        $data['chart1'] = new LaravelChart($chart_options);

        return view('members.dashboard', $data);
    }

    /**
     * Display a member's dashboard by using the awesomplete search feature
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboardSearch(Request $request) {
        // dd($request->all());
        $data['member'] = Member::where('ippis', $request->search)->first();
        $chart_options = [
            'chart_title' => 'Transactions by dates',
            'report_type' => 'group_by_date',
            'model' => 'App\Ledger',
            'group_by_field' => 'created_at',
            'group_by_period' => 'day',
            'aggregate_function' => 'sum',
            'aggregate_field' => 'savings_bal',
            'chart_type' => 'line',
        ];
        $data['chart1'] = new LaravelChart($chart_options);

        return view('members.dashboard', $data);
    }

    /**
     * Display a member's ledger.
     *
     * @return \Illuminate\Http\Response
     */
    public function ledger(Request $request, $ippis) {
        // dd($request, $ippis);

        $date_from = Carbon::now()->startOfYear();
        $date_to = Carbon::now()->endOfYear();
        $member = Member::where('ippis', $ippis)->first();
        $ledgerQuery = Ledger::query();

        if($member) {
            $ledgerQuery = $ledgerQuery->where('member_id', $member->id);
        }


        if ($request->date_from) {    
            $date_from = $request->date_from;
            $date_to = $request->date_to; 
        }
            
        $ledgerQuery = $ledgerQuery->whereBetween('created_at', [$date_from, $date_to]);

        $data['member'] = $member;
        $data['date_from'] = $date_from;
        $data['date_to'] = $date_to;
        $ledgers = $ledgerQuery->get();
        $data['ledgers'] = $ledgers;


        return view('members.ledger', $data);
    }

    /**
     * Add a member
     */
    function addMember() {
        $data['centers'] = Center::pluck('name', 'id');

        return view('members.add', $data);
    }

    /**
     * Save a member
     */
    function saveMember(Request $request) {
        // dd($request->all());

        $rules = [
            'fname' => 'required',
            'lname' => 'required',
            'ippis' => 'required|unique:members,ippis',
            'monthly_savings' => 'required',
            'pay_point' => 'required',
            'phone' => 'required',
            'email' => 'email|unique:members,email',

        ];

        $messages = [
            'fname.required' => 'The first name is required',
            'lname.required' => 'The last name is required',
            'ippis.required' => 'This IPPIS Number is required',
            'ippis.unique'   => 'This IPPIS Number is already in use',
            'pay_point.required' => 'Member\'s pay point is required',
            'phone.required' => 'Member\'s phone number is required',
        ];

        $this->validate($request, $rules, $messages);

        $member              = new Member;
        $member->pf          = $request->pf;
        $member->ippis       = $request->ippis;
        $member->fname       = $request->fname;
        $member->lname       = $request->lname;
        $member->full_name   = $request->fname.' '.$request->lname;
        $member->sbu         = $request->sbu;
        $member->email       = $request->email;
        $member->phone       = $request->phone;
        $member->coop_no     = $request->coop_no;
        $member->pay_point   = $request->pay_point;
        $member->nok_name    = $request->nok_name;
        $member->nok_phone   = $request->nok_phone;
        $member->nok_address = $request->nok_address;
        $member->nok_rship   = $request->nok_rship;
        $member->save();

        // add monthly savings
        $ms                 = new MonthlySaving;
        $ms->ippis          = $member->ippis;
        $ms->amount         = $request->monthly_savings;
        $ms->save();

        // create account number
        Account::insert([
            'account_no' => $member->ippis,
            'entity_type' => 'p',
        ]);

        // add as user        
        $user               = new User;
        $user->name         = $member->full_name;
        $user->username     = $member->ippis;
        $user->ippis        = $member->ippis;
        $user->password     = \Hash::make($user->ippis);
        $user->save();

        return redirect('members');
    }

    /**
     * Edit a member
     */
    public function editMember($ippis) {

        $data['centers'] = Center::pluck('name', 'id');
        $data['member'] = Member::where('ippis', $ippis)->first();

        return view('members.edit', $data);
    }

    /**
     * Save a member
     */
    function updateMember(Request $request, $ippis) {
        // dd($request->all(), $ippis);

        $rules = [
            'fname' => 'required',
            'lname' => 'required',
            'ippis' => 'required',
            // 'monthly_savings' => 'required',
            'pay_point' => 'required',
            'phone' => 'required',
            'email' => 'email|unique:members,email',
        ];

        $messages = [
            'fname.required' => 'The first name is required',
            'lname.required' => 'The last name is required',
            'ippis.required' => 'This IPPIS Number is required',
            'ippis.unique'   => 'This IPPIS Number is already in use',
            'pay_point.required' => 'Member\'s pay point is required',
            'phone.required' => 'Member\'s phone number is required',
        ];

        $this->validate($request, $rules, $messages);

        $member = Member::where('ippis', $ippis)->first();
        $member->pf = $request->pf;
        $member->ippis = $request->ippis;
        $member->fname = $request->fname;
        $member->lname = $request->lname;
        $member->full_name = $request->fname.' '.$request->lname;
        $member->sbu = $request->sbu;
        $member->email = $request->email;
        $member->phone = $request->phone;
        $member->coop_no = $request->coop_no;
        $member->pay_point = $request->pay_point;
        $member->center_id = $request->center_id;
        $member->nok_name = $request->nok_name;
        $member->nok_phone = $request->nok_phone;
        $member->nok_address = $request->nok_address;
        $member->nok_rship = $request->nok_rship;
        $member->save();

        // create account number
        $account = Account::where('account_no', $member->ippis)->first();
        $account->account_no = $member->ippis;
        $account->entity_type = 'p';
        $account->save();
        
        // add as user        
        $user               = User::where('ippis', $member->ippis)->first();
        $user->name         = $member->full_name;
        $user->username     = $member->ippis;
        $user->ippis        = $member->ippis;
        $user->email        = $member->email;
        $user->save();


        $activityLog = ActivityLog::where('ippis', $request->old_ippis)->first();
        if($activityLog) {
            $activityLog->ippis = $request->ippis;
            $activityLog->save();
        }
        $MonthlySaving = MonthlySaving::where('ippis', $request->old_ippis)->first();
        if($MonthlySaving) {
            $MonthlySaving->ippis = $request->ippis;
            $MonthlySaving->save();
        }
        $MonthlySavingsPayment = MonthlySavingsPayment::where('ippis', $request->old_ippis)->first();
        if($MonthlySavingsPayment) {
            $MonthlySavingsPayment->ippis = $request->ippis;
            $MonthlySavingsPayment->save();
        }
        $LongTerm = LongTerm::where('ippis', $request->old_ippis)->first();
        if($LongTerm) {
            $LongTerm->ippis = $request->ippis;
            $LongTerm->save();
        }
        $LongTermLoanDefault = LongTermLoanDefault::where('ippis', $request->old_ippis)->first();
        if($LongTermLoanDefault) {
            $LongTermLoanDefault->ippis = $request->ippis;
            $LongTermLoanDefault->save();
        }
        $LongTermPayment = LongTermPayment::where('ippis', $request->old_ippis)->first();
        if($LongTermPayment) {
            $LongTermPayment->ippis = $request->ippis;
            $LongTermPayment->save();
        }
        $ShortTerm = ShortTerm::where('ippis', $request->old_ippis)->first();
        if($ShortTerm) {
            $ShortTerm->ippis = $request->ippis;
            $ShortTerm->save();
        }
        $ShortTermLoanDefault = ShortTermLoanDefault::where('ippis', $request->old_ippis)->first();
        if($ShortTermLoanDefault) {
            $ShortTermLoanDefault->ippis = $request->ippis;
            $ShortTermLoanDefault->save();
        }
        $ShortTermPayment = ShortTermPayment::where('ippis', $request->old_ippis)->first();
        if($ShortTermPayment) {
            $ShortTermPayment->ippis = $request->ippis;
            $ShortTermPayment->save();
        }
        $Commodity = Commodity::where('ippis', $request->old_ippis)->first();
        if($Commodity) {
            $Commodity->ippis = $request->ippis;
            $Commodity->save();
        }
        $CommodityLoanDefault = CommodityLoanDefault::where('ippis', $request->old_ippis)->first();
        if($CommodityLoanDefault) {
            $CommodityLoanDefault->ippis = $request->ippis;
            $CommodityLoanDefault->save();
        }
        $CommodityPayment = CommodityPayment::where('ippis', $request->old_ippis)->first();
        if($CommodityPayment) {
            $CommodityPayment->ippis = $request->ippis;
            $CommodityPayment->save();
        }
        

        Toastr::success('Edit successful', 'Success', ["positionClass" => "toast-bottom-right"]);
        // return redirect('members');
        return redirect()->route('members.dashboard', $member->ippis);
    }

    /**
     * Savings
     */
    function savings(Member $member) {
        $data['savings'] = $member->monthly_savings;

        return view('members.savings', $data);
    }

    /**
     * search for a member
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function awesomplete(Request $request)
    {
        $user = \Auth::user();
        $searchTerm = ' '.$request->get('q');

        return Member::search($searchTerm)->get();
        
    }

    public function status($ippis) {
        $member = Member::where('ippis', $ippis)->first();

        $qualifyToDeactivate = $this->performDeactivationCheck($member);

        if (!$qualifyToDeactivate) {
            Toastr::error('Cannot deactivate member', 'Success', ["positionClass" => "toast-bottom-right"]);
            return redirect()->back();
        }

        $user = USer::where('ippis', $ippis)->first();
        $user->is_activated = $member->is_active;
        $user->is_active    = $member->is_active;
        $user->save();

        return redirect()->back();
    }

    /**
     * Check if member qualifies for deactivation
     * @param \App\Member $member
     * @return boolean
     */
    public function performDeactivationCheck(Member $member) {

        $MonthlySavingPayment = $member->latest_monthly_savings_payment();
        $LongTermPayment = $member->latest_long_term_payment();
        $ShortTermPayment = $member->latest_short_term_payment();
        $CommodityPayment = $member->latest_commodities_payment();

        $totalLoan = ($LongTermPayment->bal + $ShortTermPayment->bal + $CommodityPayment->bal);

        if ($totalLoan == 0) {
            $member->is_active = 0;
            $member->save();

            return true;
        }

        if ($MonthlySavingPayment->bal < $totalLoan) {
            $member->is_active = 1;
            $member->save();
            
            return false;
        }
        
        $ledger4trxnNumber = new Ledger;
        $trxnNumber = $ledger4trxnNumber->generateTrxnNumber();

        $MonthlySavings = $member->latest_monthly_saving();
        $LongTermLoan = $member->latest_long_term_loan();
        $ShortTermLoan = $member->latest_short_term_loan();
        $CommodityLoan = $member->latest_commodity_loan();
        
        if ($LongTermPayment->bal > 0) {
            // make entry in long term payments table
            $ltlPayment                = new LongTermPayment;
            $ltlPayment->trxn_number   = $trxnNumber;
            $ltlPayment->trxn_type     = 'ltl_Rp_Savings';
            $ltlPayment->is_authorized = 1;
            $ltlPayment->ippis         = $member->ippis;
            $ltlPayment->pay_point     = $member->pay_point;
            $ltlPayment->ref           = 'Account deactivation';
            $ltlPayment->deposit_date  = Carbon::now();
            $ltlPayment->long_term_id  = $LongTermLoan->id;
            $ltlPayment->dr            = 0.00;
            $ltlPayment->cr            = $LongTermPayment->bal;
            $ltlPayment->bal           = 0.00;
            $ltlPayment->month         = Carbon::today()->format('m');
            $ltlPayment->year          = Carbon::today()->format('Y');
            $ltlPayment->done_by       = auth()->user()->ippis;
            $ltlPayment->save();
            // fire event to save trxn in ledger transactions
            $ledgerInternal = new Ledger_Internal;
            $ledgerInternal->recordLTLRepaymentViaSavings($member, $LongTermPayment->bal, $member->ippis.' LTL Repay. Sav.');
        }

        if ($ShortTermPayment->bal > 0) {
            // make entry in short term payments table
            $stlPayment                = new ShortTermPayment;
            $stlPayment->trxn_number   = $trxnNumber;
            $stlPayment->trxn_type     = 'stl_Rp_Savings';
            $stlPayment->is_authorized = 1;
            $stlPayment->ippis         = $member->ippis;
            $stlPayment->pay_point     = $member->pay_point;
            $stlPayment->ref           = 'Account deactivation';
            $stlPayment->deposit_date  = Carbon::now();
            $stlPayment->short_term_id = $ShortTermLoan->id;
            $stlPayment->dr            = 0.00;
            $stlPayment->cr            = $ShortTermPayment->bal;
            $stlPayment->bal           = 0.00;
            $stlPayment->month         = Carbon::today()->format('m');
            $stlPayment->year          = Carbon::today()->format('Y');
            $stlPayment->done_by       = auth()->user()->ippis;
            $stlPayment->save();
            // fire event to save trxn in ledger transactions
            $ledgerInternal = new Ledger_Internal;
            $ledgerInternal->recordSTLRepaymentViaSavings($member, $ShortTermPayment->bal, $member->ippis.' STL Repay. Sav.');
        }

        if ($CommodityPayment->bal > 0) {
            // make entry in short term payments table
            $commodityPayment                = new CommodityPayment;
            $commodityPayment->trxn_number   = $trxnNumber;
            $commodityPayment->trxn_type     = 'coml_Rp_Savings';
            $commodityPayment->is_authorized = 1;
            $commodityPayment->ippis         = $member->ippis;
            $commodityPayment->pay_point     = $member->pay_point;
            $commodityPayment->ref           = 'Account deactivation';
            $commodityPayment->deposit_date  = Carbon::now();
            $commodityPayment->commodity_id  = $CommodityLoan->id;
            $commodityPayment->dr            = 0.00;
            $commodityPayment->cr            = $CommodityPayment->bal;
            $commodityPayment->bal           = 0;
            $commodityPayment->month         = Carbon::today()->format('m');
            $commodityPayment->year          = Carbon::today()->format('Y');
            $commodityPayment->done_by       = auth()->user()->ippis;
            $commodityPayment->save();
            // fire event to save trxn in ledger transactions
            $ledgerInternal = new Ledger_Internal;
            $ledgerInternal->recordCOMLRepaymentViaSavings($member, $CommodityPayment->bal, $member->ippis.' COML Repay. Sav.');
        }

        if ($totalLoan > 0) {
            // make entry in monthly savings payments table
            $msPayment                    = new MonthlySavingsPayment;
            $msPayment->monthly_saving_id = $MonthlySavings->id;
            $msPayment->trxn_number       = $trxnNumber;
            $msPayment->trxn_type         = 'wd_S';
            $msPayment->is_authorized     = 1;
            $msPayment->ippis             = $member->ippis;
            $msPayment->pay_point         = $member->pay_point;
            $msPayment->ref               = 'Account deactivation';
            $msPayment->withdrawal_date   = Carbon::now();
            $msPayment->deposit_date      = Carbon::now();
            $msPayment->dr                = $totalLoan;
            $msPayment->cr                = 0.00;
            $msPayment->bal               = $MonthlySavingPayment->bal - $totalLoan;
            $msPayment->month             = Carbon::today()->format('m');
            $msPayment->year              = Carbon::today()->format('Y');
            $msPayment->done_by           = auth()->user()->ippis;
            $msPayment->save();
        }

        // make ledger entry
        $ledger = new Ledger;
        $ledger->trxn_number    = $trxnNumber;
        $ledger->trxn_type      = 'ltl_Rp_Savings';
        $ledger->is_authorized  = 1;
        $ledger->member_id      = $member->id;
        $ledger->pay_point      = $member->pay_point;
        $ledger->date           = Carbon::today()->format('Y-m-d');
        $ledger->ref            = 'Account deactivation';
        $ledger->deposit_date   = Carbon::now();
        $ledger->savings_dr     = 0.00;
        $ledger->savings_cr     = 0.00;
        $ledger->savings_bal    = $member->savingsBalance();
        $ledger->long_term_dr   = 0.00;
        $ledger->long_term_cr   = $LongTermPayment->bal;
        $ledger->long_term_bal  = 0.00;
        $ledger->short_term_dr  = 0.00;
        $ledger->short_term_cr  = $ShortTermPayment->bal;
        $ledger->short_term_bal = 0.00;
        $ledger->commodity_dr   = 0.00;
        $ledger->commodity_cr   = $CommodityPayment->bal;
        $ledger->commodity_bal  = 0.00;
        $ledger->done_by        = auth()->user()->ippis;
        $ledger->save();
        
        if ($MonthlySavingPayment->bal > $totalLoan) {
            $processingFee = ProcessingFee::first();
            $withdrawalSetting = WithdrawalSetting::where('type', 'withdrawal_percentage_charge')->first();

            $interest_percentage = $withdrawalSetting->value;
            $interest = $MonthlySavingPayment->bal * ($interest_percentage / 100);
            $processing_fee = $processingFee->amount;
            $bank_charges = 0;
            $net_payment = $MonthlySavingPayment->bal - ($processing_fee + $bank_charges + $interest);
            
            $msPayment                      = new MonthlySavingsPayment;
            $msPayment->trxn_number         = $trxnNumber;
            $msPayment->trxn_type           = 'wd_S';
            $msPayment->is_authorized       = 0;
            $msPayment->monthly_saving_id   = $MonthlySavings->id;
            $msPayment->ippis               = $member->ippis;
            $msPayment->pay_point           = $member->pay_point;
            $msPayment->ref                 = 'Balance withdrawal';
            $msPayment->deposit_date        = Carbon::now();
            $msPayment->withdrawal_date     = Carbon::now();
            $msPayment->dr                  = $MonthlySavingPayment->bal - $totalLoan;
            $msPayment->cr                  = 0.00;
            $msPayment->bal                 = 0.00;
            $msPayment->month               = Carbon::today()->format('m');
            $msPayment->year                = Carbon::today()->format('Y');
            $msPayment->is_withdrawal       = 1;
            $msPayment->processing_fee      = $processing_fee;
            $msPayment->bank_charges        = $bank_charges;
            $msPayment->interest            = $interest;
            $msPayment->interest_percentage = $interest_percentage;
            $msPayment->net_payment         = $net_payment;
            $msPayment->pv_number           = $msPayment->pvNumberGenerator();
            $msPayment->done_by             = auth()->user()->ippis;
            $msPayment->save();

            // make ledger entry
            $ledger = new Ledger;
            $ledger->trxn_number    = $trxnNumber;
            $ledger->trxn_type      = 'wd_S';
            $ledger->is_authorized  = 0;
            $ledger->member_id      = $member->id;
            $ledger->pay_point      = $member->pay_point;
            $ledger->date           = Carbon::today()->format('Y-m-d');
            $ledger->ref            = 'Balance withdrawal';
            $ledger->deposit_date   = Carbon::now();
            $ledger->savings_dr     = $MonthlySavingPayment->bal - $totalLoan;
            $ledger->savings_cr     = 0.00;
            $ledger->savings_bal    = 0.00;
            $ledger->long_term_dr   = 0.00;
            $ledger->long_term_cr   = 0.00;
            $ledger->long_term_bal  = 0.00;
            $ledger->short_term_dr  = 0.00;
            $ledger->short_term_cr  = 0.00;
            $ledger->short_term_bal = 0.00;
            $ledger->commodity_dr   = 0.00;
            $ledger->commodity_cr   = 0.00;
            $ledger->commodity_bal  = 0.00;
            $ledger->done_by        = auth()->user()->ippis;
            $ledger->save();
        }

        $member->is_active = 0;
        $member->save();

        return true;
    }

    /**
     * Get the balances of member accounts at the point of attempted deactivation
     */
    public function deactivationSummary($ippis) {
        $member = Member::where('ippis', $ippis)->firstOrFail();

        $data['MonthlySavingPayment'] = $member->latest_monthly_savings_payment();
        $data['LongTermPayment'] = $member->latest_long_term_payment();
        $data['ShortTermPayment'] = $member->latest_short_term_payment();
        $data['CommodityPayment'] = $member->latest_commodities_payment();

        $data['totalLoan'] = ($data['LongTermPayment']->bal + $data['ShortTermPayment']->bal + $data['CommodityPayment']->bal);
        $data['member'] = $member;

        return view('members.deactivationSummary', $data);
    }

    /**
     * Show form for member bio update
     *
     * @param  \App\Ledger  $ledger
     * @return \Illuminate\Http\Response
     */
    public function updateMemberInformation(Ledger $ledger)
    {
        return view('members.update');
    }

    /**
     * Post member update information
     *
     * @param  \App\Ledger  $ledger
     * @return \Illuminate\Http\Response
     */
    public function postUpdateMemberInformation(Request $request, Ledger $ledger) {
        // dd($request->all());

        $rules = [
            'file' => 'required',
        ];

        $messages = [
            'file.required' => 'Please select a file',
        ];

        $this->validate($request, $rules, $messages);

        $excel = Importer::make('Excel');
        $excel->load(request()->file('file'));
        $rows = $excel->getCollection();

        // remove hearder
        unset($rows[0]);

        // dd($rows);

        foreach($rows as $row) {

            $ippis      = $row[0];
            $phone      = ($row[3]);
            $email      = ($row[4]);
            $nokName    = ($row[5]);
            $nokPhone   = ($row[6]);

            $member = Member::where('ippis', $ippis)->first();

            // Member edit
            if ($member) {
                $member->phone       = $phone;
                $member->email       = $email;
                $member->nok_name    = ucfirst($nokName);
                $member->nok_phone   = $nokPhone;
                $member->save();
            }

        }

        // Toastr::success('Update successful', 'Success', ["positionClass" => "toast-bottom-right"]);
        flash('Update successful')->success();
        return redirect()->back();
    }

    public function register() {
        $data['centers'] = Center::pluck('name', 'id');

        return view('members.register', $data);
    }

    public function registerDownload(Request $request) {
        // dd($request->all());
        $centerId = $request->center_id;
        $center = Center::find($centerId);
        
        if ($center) {
            return Excel::download(new MembersRegisterExport($centerId), 'Member_Register_'.$center->name.'.csv');
        } else {
            return Excel::download(new MembersRegisterExport($centerId), 'Member_Register_ALL_CENTERS.csv');
        }

        
    }
}
