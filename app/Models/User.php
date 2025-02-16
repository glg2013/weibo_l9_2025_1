<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($user){
            $user->activation_token = Str::random(10);
        });
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * 生成用户的头像
     * @return string
     */
    public function gravatar($size = '100')
    {
        //$hash = md5(strtolower(trim($this->attributes['email'])));
        $hash = md5( strtolower( trim( 'summer@example.com' ) ) );
        //return "https://cdn.v2ex.com/gravatar/$hash?s=$size";
        // 上面的连接不可用，所有改用本地图片
        return 'http://weibo_l9_2025_1.test/avatar/' . rand(1, 8) .'.png';
    }

    public function statuses()
    {
        return $this->hasMany(Status::class);
    }
}
