<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatusesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // 创建微博
    public function store(Request $request)
    {
        $this->validate($request, [
            'content' => 'required|max:140'
        ]);

        Auth::user()->statuses()->create([
            'content' => $request->get('content')
        ]);

        session()->flash('success', '发布成功！');

        return redirect()->back();
    }

    // 删除微博
    public function destroy(Status $status)
    {
        $this->authorize('destroy', $status);
        $status->delete();

        session()->flash('success', '微博已被成功删除！');
        return redirect()->back();
    }
}
