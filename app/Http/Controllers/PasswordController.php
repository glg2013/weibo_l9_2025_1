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
}
