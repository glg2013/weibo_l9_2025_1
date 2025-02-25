<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // 调用用户表数据填充类
        $this->call(UsersTableSeeder::class);
        // 调用微博数据填充类
        $this->call(StatusesTableSeeder::class);
        // 调用关注者数据填充类
        $this->call(FollowersTableSeeder::class);

    }
}
