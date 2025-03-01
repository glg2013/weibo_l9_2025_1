<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FollowersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();
        $user = $users->first();
        $user_id = $user->id;

        // 获取去除掉 ID 为 1 的所有用户 ID 数组
        $followers = $users->slice(1);
        $follower_ids = $followers->pluck('id')->toArray();

        // 关注除 1 号用户外的所有用户
        $user->follow($follower_ids);

        // 除 1 号用户外的所有用户都来关注 1 号用户
        foreach ($followers as $follower) {
            $follower->follow($user_id);
        }
    }
}
