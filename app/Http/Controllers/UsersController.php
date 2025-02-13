<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', [
            'except' => ['show', 'create', 'store', 'index']
        ]);

        // 只让未登录用户访问注册页面
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
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

        // 自动登录
        Auth::login($user);
        // 注册成功后提示
        session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
        return redirect()->route('users.show', [$user]);
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
}
