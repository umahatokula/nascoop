<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\Member;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use DB;
use Toastr;

class UsersController extends Controller
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
    $data['title'] = 'Manage Users';
    $data['usersMenu'] = 1;
    $data['members'] = Member::pluck('full_name', 'id');
    $data['roles'] = Role::pluck('name', 'id');
    $data['permissions'] = [];
    $data['users'] = User::with('roles')->paginate(20);

    return view('users.index', $data);
  }

  /**
  * Show the form for creating a new resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function create()
  {
    $members = Member::all();
    $roles = Role::all();
    $permissions = Permission::all();

    if(request()->ajax()) {
      return ['members' => $members, 'roles' => $roles, 'permissions' => $permissions];
    }

    $data['members'] = $members;
    $data['roles'] = $roles;
    $data['permissions'] = $permissions;

    return view('users.create', $data);
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
        // 'ippis' => 'required|unique:users,username',
        'ippis' => 'required',
        'roles' => 'required',
    ];

    $messages = [
        'ippis.required' => 'Please select a member',
        'ippis.unique' => 'It seems this member has already been added as a user',
        'roles.required' => 'Please select roles for this member',
    ];

    $this->validate($request, $rules, $messages);

    //get the Members details
    $member = Member::where('ippis', $request->ippis)->first();

    //create new user
    $user  = User::where('username', $member->ippis)->first();

    if($user) {
      $user->username 		= $member->ippis;
      $user->password     = \Hash::make($member->ippis);
      $user->name 		    = $member->full_name;
      $user->ippis 	      = $member->ippis;
      $user->save();
    } else {
      $user               = new User;
      $user->username 		= $member->ippis;
      $user->password     = \Hash::make($member->ippis);
      $user->name 		    = $member->full_name;
      $user->ippis 	      = $member->ippis;
      $user->save();
    }
      

    //assign new user to role(s)
    $roles = [];
    foreach ($request->roles as $role) {
      $roles[] = $role['name'];
    }
    $user->syncRoles($roles);

    //revoke permission(s) from user
    foreach ($request->droppedPermissions as $droppedPermissions) {
      $user->revokePermissionTo($droppedPermissions['name']);
    }

    if($request->ajax()){
      return response()->json(['success' => true, 'message' => 'User added'], 200);
    }

    return redirect('users/create');

  }

  /**
  * Display the specified resource.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function show(Request $request, $id)
  {
    dd($id);
    if($request->ajax()) {
      // id here is user id
      return User::with('member')->where('id', $id)->first();
    }

    $data['usersMenu'] = 1;
    // id here is member id -- i dont remember why i did this
    $data['user'] = User::with('member')->where('member_id', $id)->first();

    return view('users.show', $data);
  }

  /**
  * Show the form for editing the specified resource.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function edit($id)
  {
    $data['title'] = 'Edit Users';
    $data['usersMenu'] = 1;
    $data['members'] = Member::select(\DB::raw('concat (fname,lname) as full_name, id'))->pluck('full_name', 'id');
    $data['roles'] = Role::where('slug', '!=', 'ovalsoft')->pluck('name', 'id');
    $data['user'] = User::find($id);

    //get the id(s) of the roles of this user in an array
    $roles = array();
    foreach ($data['user']->roles as $value) {
      $roles[] = $value->id;
    }
    $data['user_roles'] = $roles;

    $data['permissions'] = [];
    return view('users.edit', $data);
  }

  /**
  * Update the specified resource in storage.
  *
  * @param  \Illuminate\Http\Request  $request
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function update(Request $request, $id)
  {
    // dd($request->all());

    $rules = [
      'assign_roles' => 'required',
    ];

    $messages = [
      'assign_roles.required' => 'Select at least one role',
    ];

    $validator = \Validator::make($request->all(), $rules, $messages);

    if($validator->fails()){

      if ($request->ajax()) {
        return response()->json(['success' => FALSE, 'message' => $validator->errors()->first('assign_roles') ]);
      }

      session()->put('flash_message', 'Something went wrong. User was not added.');
      return \Redirect::back()->withInput()->withErrors($validator);

    }

    //get the Members details
    $member = Member::find($request->member_id);

    //get user
    $user = User::find($id);

    //delete existing roles for this user
    DB::table('role_user')->where('user_id', $user->id)->delete();


    //assign new user to role(s)
    foreach ($request->assign_roles as $role_id) {

      $role = Role::find($role_id);

      //assign user this role
      $user->attachRole($role);
    }

    if($request->ajax()){
      return response()->json(['success' => true, 'message' => 'User added']);
    }

    session()->flash('successMessage', 'User was successfully updated.');
    return redirect('users/create');
  }

  /**
  * Remove the specified resource from storage.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function destroy($id)
  {
    dd($id);
    $user = User::destroy($id);
    return redirect('users');
  }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {

        $user = User::find($id);

        if ($user) {
            $user = $user->delete();

        } else {

            if (\Request::ajax()) {
                return response()->json(['message' => 'User was not found']);
            }

            session()->flash('errorMessage', 'User was not found.');
            return redirect()->back();
        }

        if (\Request::ajax()) {
            return response()->json(['message' => 'User deleted']);
        }

        session()->flash('successMessage', 'User deleted.');
        return redirect()->back();

    }


  /**
  * Activate Resource
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function activate($id)
  {
    $user = User::find($id);
    $user->status_id = 1;
    $user->save();

    return redirect('users');
  }


  /**
  * Deactivate Resource
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function deactivate($id)
  {
    $user = User::find($id);
    $user->status_id = 2;
    $user->save();

    return redirect('users');
  }


  /**
  * show form to change password
  * @param  Request $request [description]
  * @return [type]           [description]
  */
  public function changePassword() {
    return view('users.changePassword');
  }


  /**
  * store changed password
  * @param  Request $request [description]
  * @return [type]           [description]
  */
  public function storeChangedPassword(Request $request) {
    // dd($request->all());
    $data['usersMenu'] = 1;
    //password update.
    $now_password       = $request->now_password;
    $password           = $request->password;
    $passwordconf       = $request->password_confirmation;
    $ippis                 = $request->ippis;

    $rules = array(
      'now_password'          => 'required',
      'password'              => 'required|min:5|confirmed|different:now_password',
      'password_confirmation' => 'required_with:password|min:5'
    );

    $messages = array(
      'now_password.required' => 'Your current password is required',
      'password.required' => 'Your new password is required',
      'password.confirmed' => 'New password and confirmationn must match',
      'password.different' => 'You new password must be different from current password',
      'password.min' => 'New passwordmust be at least 5 characters' );


      $validator = \Validator::make($request->only('now_password', 'password', 'password_confirmation'), $rules, $messages);

      if ($validator->fails()) {

        return redirect()->back()->withErrors($validator);

      } elseif (\Hash::check($now_password, \Auth::user()->password)) {

        $user = User::where('ippis', $ippis)->first();
        $user->password = \Hash::make($password);
        $user->save();

        Toastr::success('Password changed successfully.', 'Success', ["positionClass" => "toast-bottom-right"]);
        return redirect()->back();

      } else  {


        Toastr::error('Old password is incorrect', 'Error', ["positionClass" => "toast-bottom-right"]);
        return redirect()->back();

      }

      return view('users.changePassword');
    }
  }
