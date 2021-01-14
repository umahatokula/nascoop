<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Supplier;

class SupplierController extends Controller
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
        $data['suppliers'] = Supplier::all();

        return view('suppliers.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */    
    public function store(Request $request) {
        
        // dd($request->all());

        $rules = [
            'fname' => 'required',
            'lname' => 'required',
            // 'address' => 'required',
            'phone' => 'required',
            'email' => 'email',

        ];

        $messages = [
            'fname.required' => 'The first name is required',
            'lname.required' => 'The last name is required',
            'address.required' => 'This IPPIS Number is required',
            'phone'   => 'This phone number is required',
        ];

        $this->validate($request, $rules, $messages);

        $supplier = new Supplier;
        $supplier->fname = $request->fname;
        $supplier->lname = $request->lname;
        $supplier->email = $request->email;
        $supplier->phone = $request->phone;
        $supplier->address = $request->address;
        $supplier->save();

        return redirect('suppliers');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function show(Supplier $supplier)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function edit(Supplier $supplier)
    {
        $data['supplier'] = $supplier;

        return view('suppliers.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Supplier $supplier)
    {

        $rules = [
            'fname' => 'required',
            'lname' => 'required',
            // 'address' => 'required',
            'phone' => 'required',
            'email' => 'email',

        ];

        $messages = [
            'fname.required' => 'The first name is required',
            'lname.required' => 'The last name is required',
            'address.required' => 'This IPPIS Number is required',
            'phone'   => 'This phone number is required',
        ];

        $this->validate($request, $rules, $messages);

        $supplier = Supplier::find($supplier->id);
        $supplier->fname = $request->fname;
        $supplier->lname = $request->lname;
        $supplier->email = $request->email;
        $supplier->phone = $request->phone;
        $supplier->address = $request->address;
        $supplier->save();

        return redirect('suppliers');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $supplier = Supplier::find($id);
        if($supplier) {
            $supplier->delete();
        }

        return redirect('suppliers');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $supplier = Supplier::find($id);
        dd($supplier);
    }
}
