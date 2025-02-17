<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', [
            'except' => ['show', 'create', 'store', 'index', 'confirmEmail']
        ]);

        // 只让未登录用户访问注册页面
        $this->middleware('guest', [
            'only' => ['create']
        ]);

        // 限流 一个小时内最多 十 次
        $this->middleware('throttle:10,60', [
            'only' => ['store']
        ]);
    }

    public function index()
    {
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function show(User $user)
    {
        $statuses = $user->statuses()
                         ->orderBy('created_at', 'desc')
                         ->paginate(10);

        return view('users.show', compact(['user', 'statuses']));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|alpha_num:ascii|unique:users|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        /**
         * 账号创建成功后，自动登录，跳转到个人中
         *
        // 自动登录
        Auth::login($user);
        // 注册成功后提示
        session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
        return redirect()->route('users.show', [$user]);
        */

        /**
         * 账号创建成功后，发送激活邮件
         */
        $this->sendEmailConfirmationTo($user);
        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
        return redirect()->to('/');
    }

    public function edit(User $user)
    {
        // 授权
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        // 授权
        $this->authorize('update', $user);
        $this->validate($request, [
            //'name' => 'required|alpha_num|unique:users|max:50',
            'password' => 'nullable|confirmed|min:6',
        ]);

        $data = [];
        if ($request->name) {
            $data['name'] = $request->name;
        }
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }

        if ($data) {
            $request = $user->update($data);
            if ($request) {
                session()->flash('success', '个人资料更新成功！');
            }
        } else {
            session()->flash('success', '个人资料未做任何修改！');
        }

        return redirect()->route('users.show', [$user]);
    }

    public function destroy(User $user)
    {
        // 授权
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '用户已被成功删除！');
        return redirect()->back();
    }

    protected function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        //$from = 'admin@example.com';
        //$name = 'Feng';
        $to = $user->email;
        $subject = "感谢注册 Weibo 应用！请确认你的邮箱。";

        Mail::send($view, $data, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });
    }

    public function confirmEmail($token) {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->email_verified_at = now();
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('users.show', [$user]);
    }

    // 关注人列表
    public function followings(User $user)
    {
        $users = $user->followings()->paginate(30);
        $title = $user->name . '关注的人';
        return view('users.show_follow', compact('users', 'title'));
    }

    // 粉丝列表
    public function followers(User $user)
    {
        $users = $user->followers()->paginate(30);
        $title = $user->name. '的粉丝';
        return view('users.show_follow', compact('users', 'title'));
    }

}
