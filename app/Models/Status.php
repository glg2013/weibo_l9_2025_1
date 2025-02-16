<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    // 防止批量赋值错误
    protected $fillable = ['content'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
