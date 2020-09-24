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
                'startdate'=>'required',
                'enddate'=>'required',
            ];
            //自定义消息
            $messages = [
               // 'username.required' => '学生号不能为空',
               // 'name.required' => '姓名不能为空',
               // 'classname.required' => '班级不能为空',
                'project.required' => '项目名称不能为空',
                'grade.required' => '成绩不能为空',
                'startdate.required' => '开始时间不能为空',
                'enddate.required' => '结束时间不能为空',
            ];

            $this->validate($request, $rules, $messages);
            $user = \Auth::user();
            //$user = Session::get('user');
            $basis = new Basis();
            $basis->username = $user->username;
            $basis->name = $user->name;
            $basis->classname = !isset($user->classname)?'':$user->classname;
            $basis->role = $user->role;
            $basis->project = $request->input('project');
            $basis->childproject = $request->input('childproject');
            $basis->grade = $request->input('grade');
            $basis->startdate = $request->input('startdate');
            $basis->enddate = $request->input('enddate');
            $res = $this->timediff($request->input('startdate'),$request->input('enddate'));

            $basis->status = 1;
            $basis->issuserid = '1000000009';
            $basis->fileid = '243422';


            $basis->hour = $res['hour'];
            $basis->minute = $res['min'];

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


    //成绩列表
    public function lookbasis(Request $request){
        $user = \Auth::user();
        //$user = Session::get('user');
        $role = $user->role;
        if($role == 'teacher'){
            //$basislist = Basis::where('role','students')->paginate(20);
            $basislist = Basis::where('role','students')->get();
        }elseif($role == 'admin'){
            //$basislist = Basis::paginate(20);
            $basislist = Basis::get();
        }elseif($role == 'students'){
           //$basislist = Basis::where('username',$user->username)->paginate(20);
           $basislist = Basis::where('username',$user->username)->get();
        }
        return json_encode(['errcode'=>'1','errmsg'=>'ok','data'=>$basislist],JSON_UNESCAPED_UNICODE );
    }


    //计算两时间戳之间的时分秒

    public function timediff($begin_time,$end_time)
    {
        if($begin_time < $end_time){
            $starttime = $begin_time;
            $endtime = $end_time;
        }else{
            $starttime = $end_time;
            $endtime = $begin_time;
        }

        //计算天数
        $timediff = $endtime-$starttime;
        $days = intval($timediff/86400000); //1000 * 60 * 60 * 24
        //计算小时数
        $remain = $timediff%86400000; //1000 * 60 * 60 * 24
        $hours = intval($remain/3600000); //1000 * 60 * 60
        //计算分钟数
        $remain1 = $timediff%3600000;  //1000 * 60 * 60
        $mins = intval($remain1/60000); //1000 * 60
        //计算秒数
        $remain2 = $timediff%60000;

        $secs = intval($remain2/60000);
        $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs);
        return $res;
    }

    /**
     * 获取毫秒级别的时间戳
     */
    public function getMsecTime()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msectime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }

    /**
     * 毫秒转日期
     */
    public function getMsecToMescdate($msectime)
    {
        $msectime = $msectime * 0.001;
        if(strstr($msectime,'.')){
            sprintf("%01.3f",$msectime);
            list($usec, $sec) = explode(".",$msectime);
            $sec = str_pad($sec,3,"0",STR_PAD_RIGHT);
        }else{
            $usec = $msectime;
            $sec = "000";
        }
        $date = date("Y-m-d H:i:s.x",$usec);
        return $mescdate = str_replace('x', $sec, $date);
    }

    /**
     * 日期转毫秒
     */
    public function getDateToMesc($mescdate)
    {
        list($usec, $sec) = explode(".", $mescdate);
        $date = strtotime($usec);
        $return_data = str_pad($date.$sec,13,"0",STR_PAD_RIGHT);
        return $msectime = $return_data;
    }





}
