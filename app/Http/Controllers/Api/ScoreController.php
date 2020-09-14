<?php

namespace App\Http\Controllers\Api;

use App\Models\Basis;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class ScoreController extends Controller
{
    //labview基础知识考核成绩接口
    public function basis(Request $request){


        try {
            //规则
            $rules = [
                //'username'=>'required',
               // 'name'=>'required',
               // 'classname'=>'required',
                'project'=>'required',
                'grade'=>'required',
                'hour'=>'required',
                'minute'=>'required'
            ];
            //自定义消息
            $messages = [
               // 'username.required' => '学生号不能为空',
               // 'name.required' => '姓名不能为空',
               // 'classname.required' => '班级不能为空',
                'project.required' => '项目名称不能为空',
                'grade.required' => '成绩不能为空',
                'hour.required' => '小时数不能为空',
                'minute.required' => '分钟数不能为空',
            ];


            $this->validate($request, $rules, $messages);

            $user = Session::get('user');
            $basis = new Basis();
            $basis->username = $user->username;
            $basis->name = $user->name;
            $basis->classname = $user->classname;
            $basis->project = $request->input('project');
            $basis->grade = $request->input('grade');
            $basis->hour = $request->input('hour');
            $basis->minute = $request->input('minute');

            if($basis->save()){
                return json_encode(['errcode'=>1,'errmsg'=>'ok']);
            }else{
                return json_encode(['errcode'=>'2002','errmsg'=>'error']);
            }


        }catch (ValidationException $validationException){
            $messages = $validationException->validator->getMessageBag()->first();
            return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
        }
    }
}
