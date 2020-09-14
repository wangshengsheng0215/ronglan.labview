<?php

namespace App\Http\Middleware;

use Closure;

class CheckLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!session('loginstatus')){

            $data = ['code' => 401, 'data' => ['error' => '未登录'], 'msg' => '成功'];
            echo json_encode($data,JSON_UNESCAPED_UNICODE);exit;
        }
        return $next($request);
    }
}
