<?php

namespace App\Http\Controllers\Api;

use App\Models\Loginhistory;
use App\Models\Users;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{

    //注册接口
    public function register(Request $request){

            try {
                //规则
                $rules = [
                    'username'=>'required|unique:users,username',
                    'password'=>'required',
                    'name'=>'required',
                    'role'=>'required'
                ];
                //自定义消息
                $messages = [
                    'username.required' => '请输入工号',
                    'password.required' => '请输入密码',
                    'username.unique' => '工号已存在',
                    'name.required' => '请输入姓名',
                    'role.required' => '请输入角色'
                ];

                $this->validate($request, $rules, $messages);

                $username = $request->input('username');
                $password = $request->input('password');
                $name = $request->input('name');
                $role = $request->input('role');

                $user = new Users();
                $user->username = $username;
                $user->password =  Hash::make($password);
                $user->status = 1;
                $user->role = $role;
                $user->name = $name;
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

    //登录接口
    public function login(Request $request){
        try {
            //规则
            $rules = [
                'username'=>'required',
                'password'=>'required',
            ];

            //自定义消息
            $messages = [
                'username.required' => '请输入姓名/工号/学生号',
                'password.required' => '请输入密码',
            ];

            //$this->validate($request, $rules, $messages);
            $params = $this->validate($request, $rules, $messages);

            $username = $request->input('username');
            $password = $request->input('password');

            $user = Users::where('username',$username)->first();

            if($user){

               if ($token = Auth::guard('api')->attempt($params)){
                  //登录记录
                   //$ip = $request->ip();
                   $ip = $request->getClientIp();
                  $history = new Loginhistory();
                  $history->userid = $user->id;
                  $history->username = $username;
                  $history->ip = $ip;
                  $a = $history->save();
                  if($a){
                      Session::put('user',$user);
                      Session::put('loginstatus',true);
                      $messages = "登录成功！";
                      $data = [];
                      $data['id'] = $user->id;
                      $data['name'] = $user->name;
                      $data['username'] = $user->username;
                      $data['role'] = $user->role;
                      $data['token'] = 'bearer ' . $token;
                      return json_encode(['errcode'=>'1','errmsg'=>$messages,'data'=>$data],JSON_UNESCAPED_UNICODE );
                  }else{
                      $messages = "登录记录失败！";
                      return json_encode(['errcode'=>'1004','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
                  }
               }else{
                   $messages = "密码错误！";
                   return json_encode(['errcode'=>'1003','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
               }
            }else{
                $messages = "用户不存在请注册！";
                return json_encode(['errcode'=>'1002','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
            }
        }catch (ValidationException $validationException){
            $messages = $validationException->validator->getMessageBag()->first();
            return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
        }


    }

    //登出
    public function logout(Request $request){
        $data = Auth::guard('api')->logout();
        Session::flush('user');
        Session::flush('loginstatus');
        return json_encode(['errcode'=>1,'data'=>$data,'errmsg'=>'退出登录'],JSON_UNESCAPED_UNICODE);
    }
    //测试
    public function test(Request $request){
        try {
            // 验证规则，由于业务需求，这里我更改了一下登录的用户名，使用手机号码登录
            $rules = [
                'username' => 'required',
                'password' => 'required',
            ];

            //自定义消息
            $messages = [
                'username.required' => '请输入姓名/工号/学生号',
                'password.required' => '请输入密码',
            ];

            // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
            $params = $this->validate($request, $rules, $messages);

            // 使用 Auth 登录用户，如果登录成功，则返回 201 的 code 和 token，如果登录失败则返回
            return ($token = Auth::guard('api')->attempt($params))
                ? response(['token' => 'bearer ' . $token], 201)
                : json_encode(['errcode'=>'1003','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
        }catch (ValidationException $validationException){
            $messages = $validationException->validator->getMessageBag()->first();
            return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
            }
    }
}
