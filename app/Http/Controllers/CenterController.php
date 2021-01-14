<?php

namespace App\Http\Controllers;

use App\Center;
use Illuminate\Http\Request;
use App\Ledger_Internal;
use Toastr;

class CenterController extends Controller
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['centers'] = Center::all();

        return view('centers.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('centers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());

        $rules = [
            'name' => 'required|unique:centers',
            'transacting_bank_ledger_no' => 'required',

        ];

        $messages = [
            'name.required' => 'The name is required',
            'name.unique' => 'The name is already taken',
            'transacting_bank_ledger_no.required' => 'Please enter the acount code for the bank account',
        ];

        $this->validate($request, $rules, $messages);

        $center                             = new Center;
        $center->name                       = $request->name;
        $center->code                       = $request->code;
        $center->transacting_bank_ledger_no = $request->transacting_bank_ledger_no;
        $center->save();

        $accounts = Ledger_Internal::where('use_centers_as_detail_accounts', true)->where('usage', 'header')->get();

        foreach ($accounts as $account) {
            $children = $account->getChildren();

            $parent    = $account;
            $level     = $parent ? ($parent->level + 1) : 1;
            $parent_id = $parent ? $parent->id : 0;
            $ledger_no = $children ? $children->last()->ledger_no + 1 : $account->ledger_no + 1;


            // dd($parent, $level, $parent_id, $children);
    
            $ledgerAccount                                 = new Ledger_Internal;
            $ledgerAccount->ledger_no                      = $ledger_no;
            $ledgerAccount->account_type                   = $parent->account_type;
            $ledgerAccount->account_name                   = $parent->prefix_text.' '.$center->name;
            $ledgerAccount->usage                          = 'detail';
            $ledgerAccount->allow_manual_journal_entries   = true;
            $ledgerAccount->ignore_trailing_zeros          = true;
            $ledgerAccount->use_centers_as_detail_accounts = false;
            $ledgerAccount->description                    = $center->name;
            $ledgerAccount->level                          = $level;
            $ledgerAccount->parent_id                      = $parent_id;
            $ledgerAccount->status                         = 1;
            $ledgerAccount->save();
        }

        Toastr::success('Center added', 'Success', ["positionClass" => "toast-bottom-right"]);

        return redirect()->route('centers.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Center  $center
     * @return \Illuminate\Http\Response
     */
    public function show(Center $center)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Center  $center
     * @return \Illuminate\Http\Response
     */
    public function edit(Center $center)
    {
        $data['center'] = Center::find($center->id);

        return view('centers.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Center  $center
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Center $center)
    {
        // dd($request->all());

        $rules = [
            'name' => 'required',
            'transacting_bank_ledger_no' => 'required',

        ];

        $messages = [
            'name.required' => 'The name is required',
            'name.unique' => 'The name is already taken',
            'transacting_bank_ledger_no.required' => 'Please enter the acount code for the bank account',
        ];

        $this->validate($request, $rules, $messages);

        $center = Center::find($center->id);
        $center->name = $request->name;
        $center->code = $request->code;
        $center->transacting_bank_ledger_no = $request->transacting_bank_ledger_no;
        $center->save();

        return redirect()->route('centers.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Center  $center
     * @return \Illuminate\Http\Response
     */
    public function destroy(Center $center)
    {
        dd($center);

        $center = Center::find($center->id);
        
        if($center) {
            $center->delete();
        }

        return redirect()->route('centers.index');
    }
}
