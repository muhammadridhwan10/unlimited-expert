<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use App\Models\Employee;
use App\Models\Training;
use App\Models\Mail\UserCreate;
use App\Models\User;
use App\Models\ProjectUser;
use App\Models\ProjectTask;
use App\Models\UserCompany;
use App\Models\LoginDetail;
use App\Models\Branch;
use Auth;
use File;
use App\Models\Utility;
use App\Models\Order;
use App\Models\Plan;
use App\Models\UserToDo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Session;
use Spatie\Permission\Models\Role;



class UserController extends Controller
{

    public function index()
    {
        $user = \Auth::user();
        if(\Auth::user()->can('manage user'))
        {
            if(\Auth::user()->type = 'admin')
            {
                $users = User::where('type', '!=', 'client')->get();
            }
            elseif(\Auth::user()->type = 'company')
            {
                $users = User::where('type', '!=', 'client')->get();
            }
            else
            {
                $users = User::where('created_by', '=', $user->creatorId())->where('type', '!=', 'client')->get();
            }
            return view('user.index', compact('users'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

    }

    public function create()
    { 
        $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'user')->get();
        $user  = \Auth::user();
        $roles = Role::where('created_by', '=', $user->creatorId())->where('name','!=','client')->get()->pluck('name', 'id');
        
        $branches = Branch::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

        if(\Auth::user()->can('create user'))
        {
            return view('user.create', compact('roles', 'customFields','branches'));
        }
        else
        {
            return redirect()->back();
        }
    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('create user'))
        {
            $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->first();
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required|max:120',
                                   'email' => 'required|email|unique:users',
                                   'password' => 'required|min:6',
                                   'role' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }


            $objUser    = \Auth::user();
            $role_r                = Role::findById($request->role);
            $psw                   = $request->password;
            $request['password']   = Hash::make($request->password);
            $request['type']       = $role_r->name;
            $request['lang']       = !empty($default_language) ? $default_language->value : 'en';
            $request['created_by'] = \Auth::user()->creatorId();
            $user = User::create($request->all());
            $user->assignRole($role_r);

            if($request['type'] != 'client')
                \App\Models\Utility::employeeDetails($user->id,\Auth::user()->creatorId(), $request->branch_id);

            //Send Email

            $user->password = $psw;
            $user->type     = $role_r->name;

            $userArr = [
                'email' => $user->email,
                'password' =>  $user->password,
            ];
            $resp = Utility::sendEmailTemplate('create_user', [$user->id => $user->email], $userArr);
            return redirect()->route('users.index')->with('success', __('User successfully created.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));

        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

    }

    public function edit($id)
    {
        $user  = \Auth::user();
        $roles = Role::where('created_by', '=', $user->creatorId())->where('name','!=','client')->get()->pluck('name', 'id');
        if(\Auth::user()->can('edit user'))
        {
            $user              = User::findOrFail($id);
            $user->customField = CustomField::getData($user, 'user');
            $customFields      = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'user')->get();

            return view('user.edit', compact('user', 'roles', 'customFields'));
        }
        else
        {
            return redirect()->back();
        }

    }


    public function update(Request $request, $id)
    {

        if(\Auth::user()->can('edit user'))
        {
            if(\Auth::user()->type == 'company' || \Auth::user()->type == 'admin')
            {
                $user = User::findOrFail($id);
                $validator = \Validator::make(
                    $request->all(), [
                                       'name' => 'required|max:120',
                                       'email' => 'required|email|unique:users,email,' . $id,
                                   ]
                );
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $role = Role::findById($request->role);
                $input = $request->all();
                $input['type'] = $role->name;

                $user->fill($input)->save();
                CustomField::saveData($user, $request->customField);

                $roles[] = $request->role;
                $user->roles()->sync($roles);

                return redirect()->route('users.index')->with(
                    'success', 'User successfully updated.'
                );
            }
            else
            {
                $user = User::findOrFail($id);
                $this->validate(
                    $request, [
                                'name' => 'required|max:120',
                                'email' => 'required|email|unique:users,email,' . $id,
                                'role' => 'required',
                            ]
                );

                $role          = Role::findById($request->role);
                $input         = $request->all();
                $input['type'] = $role->name;
                $user->fill($input)->save();
                Utility::employeeDetailsUpdate($user->id,\Auth::user()->creatorId());
                CustomField::saveData($user, $request->customField);

                $roles[] = $request->role;
                $user->roles()->sync($roles);

                return redirect()->route('users.index')->with(
                    'success', 'User successfully updated.'
                );
            }
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function destroy($id)
    {


        if(\Auth::user()->can('delete user'))
        {
            $user = User::find($id);
            if($user)
            {
                    if($user->delete_status == 0)
                    {
                        $user->delete_status = 1;
                    }
                    else
                    {
                        $user->delete_status = 0;
                    }

                    $user->save();
                    
                    $employee = Employee::where(['user_id' => $user->id])->delete();
                    if($employee){
                        $delete_user = User::where(['id' => $user->id])->delete();
                        if($delete_user){
                            return redirect()->route('users.index')->with('success', __('User successfully deleted .'));
                        }else{
                            return redirect()->back()->with('error', __('Something is wrong.'));
                        }
                    }else{
                        return redirect()->back()->with('error', __('Something is wrong.'));
                    }

                return redirect()->route('users.index')->with('success', __('User successfully deleted .'));
            }
            else
            {
                return redirect()->back()->with('error', __('Something is wrong.'));
            }
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function profile()
    {
        $userDetail              = \Auth::user();
        $employee                = Employee::where('user_id', $userDetail->id)->get();
        $user_projects           = $userDetail->projects()->pluck('project_id','project_id')->toArray();
        $project                 = ProjectUser::with('project')->whereIn('project_id', $user_projects)->where('user_id', $userDetail->id);
        $get_project             = $project->get();
        $total_project           = $project->get()->count();
        $training                = Training::where('employee','=', $userDetail->employee->id);
        $get_training            = $training->get();
        $total_training          = $training->get()->count();
        $task                    = ProjectTask::whereRaw("FIND_IN_SET(?,  assign_to) > 0", [$userDetail->id]);
        $get_task                = $task->get();
        $total_user_task         = $task->get()->count();
        $userDetail->customField = CustomField::getData($userDetail, 'user');
        $customFields            = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'user')->get();

        return view('user.profile', compact('employee','get_training','total_training','get_project', 'total_project', 'get_task', 'total_user_task', 'userDetail', 'customFields'));
    }

    public function editprofile(Request $request)
    {

        $userDetail = \Auth::user();
        $user       = User::findOrFail($userDetail['id']);
        $employee   = Employee::where('user_id', $user->id)->first();

        $validator = \Validator::make(
            $request->all(), [
                        'name' => 'required|max:120',
                        'email' => 'required|email|unique:users,email,' . $userDetail['id'],
                        'profile' => 'image|mimes:jpeg,png,jpg|max:2048',
                        ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        if($request->hasFile('profile'))
        {
            $filenameWithExt = $request->file('profile')->getClientOriginalName();
            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('profile')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            $dir        = storage_path('uploads/avatar/');
            $image_path = $dir . $userDetail['avatar'];

            if(File::exists($image_path))
            {
                File::delete($image_path);
            }

            if(!file_exists($dir))
            {
                mkdir($dir, 0777, true);
            }
            $path = $request->file('profile')->storeAs('uploads/avatar/', $fileNameToStore);


        }

        if(!empty($request->profile))
        {
            $user['avatar'] = $fileNameToStore;
        }
        $user['name']  = $request['name'];
        $user['personal_description']  = $request['personal_description'];
        $user['email'] = $request['email'];
        $user->save();

        $employee->phone       = $request['phone'];
        $employee->dob         = $request['dob'];
        $employee->gender      = $request['gender'];
        $employee->address     = $request['address'];
        $employee->save();

        CustomField::saveData($user, $request->customField);

        return redirect()->back()->with('success', __('Profile successfully updated.'));
    }

    public function editphotoprofile(Request $request)
    {

        $userDetail = \Auth::user();
        $user       = User::findOrFail($userDetail['id']);

        $validator = \Validator::make(
            $request->all(), [
                        'profile' => 'image|mimes:jpeg,png,jpg|max:2048',
                        ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        if($request->hasFile('profile'))
        {
            $filenameWithExt = $request->file('profile')->getClientOriginalName();
            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('profile')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            $dir        = storage_path('uploads/avatar/');
            $image_path = $dir . $userDetail['avatar'];

            if(File::exists($image_path))
            {
                File::delete($image_path);
            }

            if(!file_exists($dir))
            {
                mkdir($dir, 0777, true);
            }
            $path = $request->file('profile')->storeAs('uploads/avatar/', $fileNameToStore);


        }

        if(!empty($request->profile))
        {
            $user['avatar'] = $fileNameToStore;
        }
        $user->save();
        CustomField::saveData($user, $request->customField);

        return redirect()->back()->with('success', __('Photo Profile successfully updated.'));
    }

    public function updatePassword(Request $request)
    {

        if(Auth::Check())
        {
            $request->validate(
                [
                    'old_password' => 'required',
                    'password' => 'required|min:6',
                    'password_confirmation' => 'required|same:password',
                ]
            );
            $objUser          = Auth::user();
            $request_data     = $request->All();
            $current_password = $objUser->password;
            if(Hash::check($request_data['old_password'], $current_password))
            {
                $user_id            = Auth::User()->id;
                $obj_user           = User::find($user_id);
                $obj_user->password = Hash::make($request_data['password']);;
                $obj_user->save();

                return redirect()->route('profile', $objUser->id)->with('success', __('Password successfully updated.'));
            }
            else
            {
                return redirect()->route('profile', $objUser->id)->with('error', __('Please enter correct current password.'));
            }
        }
        else
        {
            return redirect()->route('profile', \Auth::user()->id)->with('error', __('Something is wrong.'));
        }
    }
    // User To do module
    public function todo_store(Request $request)
    {
        $request->validate(
            ['title' => 'required|max:120']
        );

        $post            = $request->all();
        $post['user_id'] = Auth::user()->id;
        $todo            = UserToDo::create($post);


        $todo->updateUrl = route(
            'todo.update', [
                             $todo->id,
                         ]
        );
        $todo->deleteUrl = route(
            'todo.destroy', [
                              $todo->id,
                          ]
        );

        return $todo->toJson();
    }

    public function todo_update($todo_id)
    {
        $user_todo = UserToDo::find($todo_id);
        if($user_todo->is_complete == 0)
        {
            $user_todo->is_complete = 1;
        }
        else
        {
            $user_todo->is_complete = 0;
        }
        $user_todo->save();
        return $user_todo->toJson();
    }

    public function todo_destroy($id)
    {
        $todo = UserToDo::find($id);
        $todo->delete();

        return true;
    }

    // change mode 'dark or light'
    public function changeMode()
    {
        $usr = Auth::user();
        if($usr->mode == 'light')
        {
            $usr->mode      = 'dark';
            $usr->dark_mode = 1;
        }
        else
        {
            $usr->mode      = 'light';
            $usr->dark_mode = 0;
        }
        $usr->save();

        return redirect()->back();
    }

    public function upgradePlan($user_id)
    {
        $user = User::find($user_id);

        $plans = Plan::get();

        return view('user.plan', compact('user', 'plans'));
    }
    public function activePlan($user_id, $plan_id)
    {

        $user       = User::find($user_id);
        $assignPlan = $user->assignPlan($plan_id);
        $plan       = Plan::find($plan_id);
        if($assignPlan['is_success'] == true && !empty($plan))
        {
            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
            Order::create(
                [
                    'order_id' => $orderID,
                    'name' => null,
                    'card_number' => null,
                    'card_exp_month' => null,
                    'card_exp_year' => null,
                    'plan_name' => $plan->name,
                    'plan_id' => $plan->id,
                    'price' => $plan->price,
                    'price_currency' => isset(\Auth::user()->planPrice()['currency']) ? \Auth::user()->planPrice()['currency'] : '',
                    'txn_id' => '',
                    'payment_status' => 'succeeded',
                    'receipt' => null,
                    'user_id' => $user->id,
                ]
            );

            return redirect()->back()->with('success', 'Plan successfully upgraded.');
        }
        else
        {
            return redirect()->back()->with('error', 'Plan fail to upgrade.');
        }

    }

    public function userPassword($id)
    {
        $eId        = \Crypt::decrypt($id);
        $user = User::find($eId);

        return view('user.reset', compact('user'));

    }

    public function userPasswordReset(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(), [
                               'password' => 'required|confirmed|same:password_confirmation',
                           ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }


        $user                 = User::where('id', $id)->first();
        $user->forceFill([
                             'password' => Hash::make($request->password),
                         ])->save();

        return redirect()->route('users.index')->with(
            'success', 'User Password successfully updated.'
        );


    }

    public function updateStatus(Request $request)
    {
        $userId = $request->input('user_id');
        $isActive = $request->input('is_active');

        $user = User::find($userId);
        $user->is_active = $isActive;
        $user->save();

        return response()->json(['message' => 'User successfully updated.']);

    }

    //start for user login details
    public function userLog(Request $request)
    {
        $filteruser = User::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $filteruser->prepend('Select User', '');

        $query = DB::table('login_details')
            ->join('users', 'login_details.user_id', '=', 'users.id')
            ->select(DB::raw('login_details.*, users.id as user_id , users.name as user_name , users.email as user_email ,users.type as user_type'))
            ;

        if(!empty($request->month))
        {
            $query->whereMonth('date', date('m',strtotime($request->month)));
            $query->whereYear('date', date('Y',strtotime($request->month)));
        }else{
            $query->whereMonth('date', date('m'));
            $query->whereYear('date', date('Y'));
        }

        if(!empty($request->users))
        {
            $query->where('user_id', '=', $request->users);
        }
        $userdetails = $query->get();
        $last_login_details = LoginDetail::where('created_by', \Auth::user()->creatorId())->get();

        return view('user.userlog', compact( 'userdetails','last_login_details','filteruser'));
    }

    public function userLogView($id)
    {
        $users = LoginDetail::find($id);

        return view('user.userlogview', compact('users'));
    }

    public function userLogDestroy($id)
    {
        $users = LoginDetail::where('user_id', $id)->delete();
        return redirect()->back()->with('success', 'User successfully deleted.');
    }

}
