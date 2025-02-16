<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    // 显示密码重置页面
    public function showLinkRequestForm() {
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request) {
        // 1. 验证邮箱
        $request->validate(['email' => 'required|email']);
        $email = $request->get('email');

        // 2. 获取对应的用户
        $user = User::where('email', $email)->first();

        // 3.如果不存在
        if (!$user) {
            session()->flash('danger', '邮箱未注册');
        }

        // 4.生成 Token，会在视图 emais.reset_link 里拼接链接
        $token = hash_hmac('sha256', Str::random(40), config('app.key'));

        // 5.入库，使用 updateOrInsert 来保持 Email 的唯一性
        DB::table('password_resets')->updateOrInsert(['email' => $email], [
            'email' => $email,
            'token' => Hash::make($token),
            'created_at' => new Carbon(),
        ]);

        // 6.将 Token 链接发送给用户
        Mail::send('emails.reset_link', ['token' => $token], function ($message) use ($email) {
            $message->to($email);
            $message->subject('忘记密码');
        });

        session()->flash('success', '重置密码链接已发送到您的邮箱，请查收');
        return redirect()->back();
    }

    public function showResetForm(Request $request) {
        // 1.验证 Token 是否正确
        $token = $request->route()->parameter('token');
        return view('auth.passwords.reset', ['token' => $token]);
    }

    // 重置密码
    public function reset(Request $request) {
        // 1. 验证数据是否合规
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $email = $request->get('email');
        $token = $request->get('token');
        // 找回链接的有效时间
        $expires = 60 * 10;

        // 2.获取对应用户
        $user = User::where('email', $email)->first();

        // 3.如果不存咋
        if (is_null($user)) {
            session()->flash('danger', '邮箱未注册');
            return redirect()->back()->withInput();
        }

        // 4.读取重置的记录
        $record = (array)DB::table('password_resets')->where('email', $email)->first();

        // 5.记录存在
        if ($record){
            // 5.1 检查是否过期
            if (Carbon::parse($record['created_at'])->addMinutes($expires)->isPast()) {
                session()->flash('danger', '重置链接已过期');
                return redirect()->back();
            }

            // 5.2 检查 Token 是否正确
            if (!Hash::check($token, $record['token'])) {
                session()->flash('danger', '令牌错误');
                return redirect()->back();
            }

             // 5.3 一切正常，更新用户密码
            $user->update(['password' => bcrypt($request->get('password'))]);

            // 5.4 提示用户更新成功
            session()->flash('success', '密码更新成功，请重新登录');
            return redirect()->route('login');
        }

        // 6.记录不存在
        session()->flash('danger', '未找到重置记录');
        return redirect()->back();
    }
}
