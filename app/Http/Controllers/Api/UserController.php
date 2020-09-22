<?php

namespace App\Http\Controllers\Api;

use App\Imports\CommonImport;
use App\Models\Users;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    //获取用户列表
    public function userlist(Request $request){

        $user = \Auth::user();
        if($user){
            $role = $user->role;
            if($role == 'teacher'){
                $userlist = Users::where('status',1)->where('role','students')->paginate(20);
            }elseif($role == 'admin'){
                $userlist = Users::where('status',1)->whereIn('role',['students','teacher'])->paginate(20);
            }elseif($role == 'students'){
                $userlist =Users::where('status',1)->where('id',$user->id)->paginate(20);
            }
            return json_encode(['errcode'=>'1','errmsg'=>'ok','data'=>$userlist],JSON_UNESCAPED_UNICODE );
        }else{
            return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
        }

    }

    //新增用户
    public function adduser(Request $request){
        try {
            //规则
            $rules = [
                'username'=>'required|unique:users,username',
                'name'=>'required',
                'role'=>'required'
            ];
            //自定义消息
            $messages = [
                'username.required' => '请输入工号/学生号',
                'username.unique' => '工号/学生号已存在',
                'name.required' => '请输入姓名',
                'role.required' => '请输入角色'
            ];

            $this->validate($request, $rules, $messages);

            $username = $request->input('username');
            $name = $request->input('name');
            $role = $request->input('role');
            if($role == 'students'){
                $classname = $request->input('classname');
                if(!$classname){
                    return json_encode(['errcode'=>'1001','errmsg'=>'请填写班级'],JSON_UNESCAPED_UNICODE );
                }
            }else{
                $classname = '';
            }


            $user = new Users();
            $user->username = $username;
            $user->password =  Hash::make(config('userconfig.password'));
            $user->status = 1;
            $user->role = $role;
            $user->name = $name;
            $user->classname = $classname;
            $a = $user->save();
            if($a){
                return json_encode(['errcode'=>1,'errmsg'=>'ok']);
            }else{
                return json_encode(['errcode'=>'2002','errmsg'=>'error']);
            }

        }catch (ValidationException $validationException){
            $messages = $validationException->validator->getMessageBag()->first();
            return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
        }
    }

    //修改用户
    public function updateuser(Request $request){
        try {
            //规则
            $rules = [
                'id'=>'required',
                'username'=>'required',
                'name'=>'required'
            ];
            //自定义消息
            $messages = [
                'id.required' => 'id不能为空',
                'username.required' => '工号/学生号不能为空',
                'name.required' => '姓名不能为空',
            ];

            $this->validate($request, $rules, $messages);

            $id = $request->input('id');
            $username = $request->input('username');
            $name = $request->input('name');

            $a = Users::where('id',$id)->update(['username'=>$username,'name'=>$name]);

            if($a){
                return json_encode(['errcode'=>1,'errmsg'=>'ok']);
            }else{
                return json_encode(['errcode'=>'2002','errmsg'=>'error']);
            }

        }catch (ValidationException $validationException){
            $messages = $validationException->validator->getMessageBag()->first();
            return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
        }
    }

    //删除用户
    public function deleteuser(Request $request){
        try {
            //规则
            $rules = [
                'id'=>'required',
            ];
            //自定义消息
            $messages = [
                'id.required' => 'id不能为空',
            ];

            $this->validate($request, $rules, $messages);

            $id = $request->input('id');

            $a = Users::where('id',$id)->delete();

            if($a){
                return json_encode(['errcode'=>1,'errmsg'=>'ok']);
            }else{
                return json_encode(['errcode'=>'2002','errmsg'=>'error']);
            }

        }catch (ValidationException $validationException){
            $messages = $validationException->validator->getMessageBag()->first();
            return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
        }
    }

    //学生用户导入
    public function import(Request $request){

        try {
            //规则
            $rules = [
                'file'=>'required',
            ];
            //自定义消息
            $messages = [
                'file.required' => 'excel文件不能为空',
            ];
            $this->validate($request, $rules, $messages);

            $data = []; $data_repeat = [];
            $file = $request->file('file');
            if ($file->isValid()){
                $fileextension = $file->getClientOriginalExtension();
                $type = ['xls','xlsx'];
                if(in_array($fileextension,$type)){
                    $array = Excel::toArray(new CommonImport, $file);
                    foreach ($array[0] as $key=>$v){
                        if($key>='1'){
                           $data[$key-1]['name'] = $v[0];
                           $data[$key-1]['username'] = $v[1];
                           $data[$key-1]['classname'] = $v[2];
                           $data[$key-1]['password'] = hash::make(config('userconfig.password'));
                           $data[$key-1]['role'] = 'students';
                           $data[$key-1]['status'] = 1;
                           $data_repeat[] = $v[1];
                        }
                    }
                    $repeat = Users::whereIn('username',$data_repeat)->get();
                    if(!$repeat->isEmpty()){
                        foreach ($repeat as $v1){
                            foreach ($data as $k2=>$v2){
                                if(in_array($v1->username,$v2)){
                                    unset($data[$k2]);
                                }
                            }
                        }
                        $a = DB::table('users')->insert($data);
                    }else{
                        $a = DB::table('users')->insert($data);
                    }
                    if($a){
                        return json_encode(['errcode'=>1,'errmsg'=>'ok']);
                    }else{
                        return json_encode(['errcode'=>'2002','errmsg'=>'error']);
                    }

                }else{
                    return json_encode(['errcode'=>'1007','errmsg'=>'上传文件类型为xls，xlsx'],JSON_UNESCAPED_UNICODE );
                }

            }else{
                return json_encode(['errcode'=>'1006','errmsg'=>'请上传excel文件'],JSON_UNESCAPED_UNICODE );
            }



        }catch (ValidationException $validationException){
            $messages = $validationException->validator->getMessageBag()->first();
            return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
        }


    }

    //老师导入
    public function importeacher(Request $request){

        try {
            //规则
            $rules = [
                'file'=>'required',
            ];
            //自定义消息
            $messages = [
                'file.required' => 'excel文件不能为空',
            ];
            $this->validate($request, $rules, $messages);

            $data = []; $data_repeat = [];
            $file = $request->file('file');
            if ($file->isValid()){
                $fileextension = $file->getClientOriginalExtension();
                $type = ['xls','xlsx'];
                if(in_array($fileextension,$type)){
                    $array = Excel::toArray(new CommonImport, $file);
                    foreach ($array[0] as $key=>$v){
                        if($key>='1'){
                            $data[$key-1]['name'] = $v[0];
                            $data[$key-1]['username'] = $v[1];
                            $data[$key-1]['password'] = hash::make(config('userconfig.password'));
                            $data[$key-1]['role'] = 'teacher';
                            $data[$key-1]['status'] = 1;
                            $data_repeat[] = $v[1];
                        }
                    }
                    $repeat = Users::whereIn('username',$data_repeat)->get();
                    if(!$repeat->isEmpty()){
                        foreach ($repeat as $v1){
                            foreach ($data as $k2=>$v2){
                                if(in_array($v1->username,$v2)){
                                    unset($data[$k2]);
                                }
                            }
                        }
                        $a = DB::table('users')->insert($data);
                    }else{
                        $a = DB::table('users')->insert($data);
                    }
                    if($a){
                        return json_encode(['errcode'=>1,'errmsg'=>'ok']);
                    }else{
                        return json_encode(['errcode'=>'2002','errmsg'=>'error']);
                    }

                }else{
                    return json_encode(['errcode'=>'1007','errmsg'=>'上传文件类型为xls，xlsx'],JSON_UNESCAPED_UNICODE );
                }

            }else{
                return json_encode(['errcode'=>'1006','errmsg'=>'请上传excel文件'],JSON_UNESCAPED_UNICODE );
            }



        }catch (ValidationException $validationException){
            $messages = $validationException->validator->getMessageBag()->first();
            return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
        }
    }
}
