<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
class UserController extends Controller
{
    //validate and permission
    function __construct()
    {
    
        $this->middleware('permission:قائمة المستخدمين', ['only' => ['index']]);
        $this->middleware('permission:اضافة مستخدم', ['only' => ['create','store']]);
        $this->middleware('permission:تعديل مستخدم', ['only' => ['edit','update']]);
        $this->middleware('permission:حذف مستخدم', ['only' => ['destroy']]);
        
    }
    
    //main page users
    public function index(Request $request)
    {
        $data = User::orderBy('id','DESC')->get();
        return view('users.show_users',compact('data'))
        ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    //create new user
    public function create()
    {
        $roles = Role::pluck('name','name')->all();
        
        return view('users.Add_user',compact('roles'));
    }

    //store user in database
    public function store(Request $request)
    {
        if(auth()->user()->roles_name == ["owner"]){
            $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'roles_name' => 'required'
            ]);
            
            $input = $request->all();
            
            
            $input['password'] = Hash::make($input['password']);
            
            $user = User::create($input);
            $user->assignRole($request->input('roles_name'));
            return redirect()->route('users.index')
            ->with('success','تم اضافة المستخدم بنجاح');
        }
        elseif(auth()->user()->roles_name == ["admin"] || auth()->user()->roles_name !== ["admin"]){
            $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'roles_name' => 'required'
            ]);
            
            $input = $request->all();
            $valid = $input['roles_name'] = $request->roles_name;
            if($valid == ["admin"] || $valid == ["owner"]){
                
                session()->flash('error' , "هذة الاضافة غير مسموح بها ");
                return redirect()->back();
            }
            
            $input['password'] = Hash::make($input['password']);
            
            $user = User::create($input);
            $user->assignRole($request->input('roles_name'));
            return redirect()->route('users.index')
            ->with('success','تم اضافة المستخدم بنجاح');
        }
        
    }

    // show user
    public function show($id)
    {
        $user = User::find($id);
        return view('users.show',compact('user'));
    }

    //edit user
    public function edit($id)
    {
        if(auth()->user()->roles_name == ["owner"]){
            $user = User::find($id);
            $roles = Role::pluck('name','name')->all();
            $userRole = $user->roles->pluck('name','name')->all();
            return view('users.edit',compact('user','roles','userRole'));
        }
        elseif(auth()->user()->roles_name == ["admin"] || auth()->user()->roles_name !== ["admin"]){
            $user = User::find($id);
            $roles = Role::pluck('name','name')->all();
            $userRole = $user->roles->pluck('name','name')->all();
            
            if($userRole == ["owner"=>"owner"] || $userRole == ["admin"=>"admin"])
            {
                session()->flash('error' , "حدث خطا فى الخادم برجاء عدم الدخول الى اشخاص اخرىن");
                return redirect()->back();
            }
            else{
            return view('users.edit',compact('user','roles','userRole'));
                
            }
        }
    
    }

    //update user
    public function update(Request $request, $id)
    {
        if(auth()->user()->roles_name == ["owner"]){
            
            $this->validate($request, [
                'name' => 'required',
                'email' => 'required|email|unique:users,email,'.$id,
                'password' => 'same:confirm-password',
                'roles' => 'required'
            ]);
            
            $input = $request->all();
            $input['roles_name'] = $request->roles;
            
            if(!empty($input['password'])){
                 $input['password'] = Hash::make($input['password']);
            }else{
                 $input = array_except($input,array('password'));
            }
            
            $user = User::find($id);
            $user->update($input);
            
            DB::table('model_has_roles')->where('model_id',$id)->delete();
            
            $user->assignRole($request->input('roles'));
            
            return redirect()->route('users.index')
            ->with('success','تم تحديث معلومات المستخدم بنجاح');
        }
        elseif(auth()->user()->roles_name == ["admin"] || auth()->user()->roles_name !== ["admin"]){
            
            $this->validate($request, [
                'name' => 'required',
                'email' => 'required|email|unique:users,email,'.$id,
                'password' => 'same:confirm-password',
                'roles' => 'required'
            ]);
            
            $input = $request->all();
            
            $valid = $input['roles_name'] = $request->roles;
            if($valid == ["admin"] || $valid == ["owner"]){
                session()->flash('error' , "هذا التعديل ليس مسموح بة");
                return redirect()->back();
                
            }
            
            if(!empty($input['password'])){
                 $input['password'] = Hash::make($input['password']);
            }else{
                 $input = array_except($input,array('password'));
            }
            
            $user = User::find($id);
            $user->update($input);
            
            DB::table('model_has_roles')->where('model_id',$id)->delete();
            
            $user->assignRole($request->input('roles'));
            
            return redirect()->route('users.index')
            ->with('success','تم تحديث معلومات المستخدم بنجاح');
        }
    }

    //delete user
    public function destroy(Request $request)
    {
        User::find($request->user_id)->delete();
        return redirect()->route('users.index')->with('success','تم حذف المستخدم بنجاح');
    }
}