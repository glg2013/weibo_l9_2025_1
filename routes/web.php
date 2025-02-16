<?php

use App\Http\Controllers\PasswordController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\StaticPagesController;
use App\Http\Controllers\StatusesController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', function () {
//    return view('welcome');
//});

Route::get('/', [StaticPagesController::class, 'home'])->name('home');
Route::get('/home', [StaticPagesController::class, 'home'])->name('home');

Route::get('/help', [StaticPagesController::class, 'help'])->name('help');

Route::get('/about', [StaticPagesController::class, 'about'])->name('about');

Route::get('signup', [UsersController::class, 'create'])->name('signup');

// 用户资源路由
Route::get('users', [UsersController::class, 'index'])->name('users.index');
Route::get('users/create', [UsersController::class, 'create'])->name('users.create');
Route::get('users/{user}', [UsersController::class, 'show'])->name('users.show');
Route::post('users', [UsersController::class, 'store'])->name('users.store');
Route::get('users/{user}/edit', [UsersController::class, 'edit'])->name('users.edit');
Route::patch('users/{user}', [UsersController::class, 'update'])->name('users.update');
Route::delete('users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');

// 会话路由
Route::get('login', [SessionsController::class, 'create'])->name('login');
Route::post('login', [SessionsController::class, 'store'])->name('login');
Route::delete('logout', [SessionsController::class, 'destroy'])->name('logout');

// 激活令牌
Route::get('signup/confirm/{token}', [UsersController::class, 'confirmEmail'])->name('confirm_email');

// 密码重置
Route::get('password/reset', [PasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [PasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [PasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [PasswordController::class, 'reset'])->name('password.update');

// 微博路由
Route::resource('statuses', StatusesController::class, ['only' => ['store', 'destroy']]);
