<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest', [
            'only' => ['create']
        ]);

        // 限流 10 分钟内最多 十 次
        $this->middleware('throttle:10,10', [
            'only' => ['store']
        ]);
    }

    public function create()
    {
        return view('sessions.create');
    }

    public function store(Request $request)
    {
         $credentials = $this->validate($request, [
            'email' => 'required|email|max:255',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->has('remember'))) {
            if (Auth::user()->activated) {
                // 登录成功后的相关操作
                session()->flash('success', '欢迎回来！');
                //dd(session()->all());
                // 跳转到个人中心
                $fallback = route('users.show', Auth::user());
                return redirect()->intended($fallback);
            } else {
                Auth::logout();
                session()->flash('warning', '您的账号未激活，请检查邮箱中的注册邮件进行激活。');
                return redirect('/');
            }
        } else {
            // 登录失败后的相关操作
            session()->flash('danger', '很抱歉，您的邮箱和密码不匹配');
            //dd(redirect()->back()->withInput());
            // 旧的数据通过 dd 函数查看是存在 session 中的
            return redirect()->back()->withInput();
        }
    }

    public function destroy(){
        Auth::logout();
        session()->flash('success', '您已成功退出！');
        return redirect()->route('login');
    }
}
