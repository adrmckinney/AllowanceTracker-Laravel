<?php

use App\Http\Controllers\ChoreController;
use App\Http\Controllers\ForgotPasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/logout', [LogoutController::class, 'logout']);

Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm']);
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm']);
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.reset.submit');

Route::put('/user/update', [UserController::class, 'update'])->middleware('auth');

Route::post('/chore', [ChoreController::class, 'createChore'])->middleware('auth');
Route::put('/chore', [ChoreController::class, 'updateChore'])->middleware('auth');

Route::get('/permission/{id}', [PermissionsController::class, 'getPermission'])->middleware('auth');
Route::get('/permissions', [PermissionsController::class, 'getPermissions'])->middleware('auth');
Route::post('/permission/create', [PermissionsController::class, 'createPermission'])->middleware('auth');
Route::put('/permission/update', [PermissionsController::class, 'updatePermission'])->middleware('auth');
Route::post('/permission/add', [PermissionsController::class, 'addPermission'])->middleware('auth');
Route::put('/permission/remove', [PermissionsController::class, 'removePermission'])->middleware('auth');


Route::group(['namespace' => 'App\Http\Controllers'], function () {
    /**
     * Home Routes
     */
    // Route::get('/', 'HomeController@index')->name('home.index');

    Route::group(['middleware' => ['guest']], function () {
        /**
         * Register Routes
         */
        Route::get('/register', [RegisterController::class, 'show'])->name('register.show');
        // Route::post('/register', 'RegisterController@register')->name('register.perform');
        Route::post('/register', [RegisterController::class, 'register']);

        /**
         * Login Routes
         */
        Route::get('/login', [LoginController::class, 'show'])->name('login.show');
        Route::post('/login', [LoginController::class, 'login'])->name('login.perform');
    });

    Route::group(['middleware' => ['auth']], function () {
        /**
         * Logout Routes
         */
        // Route::get('/logout', [LogoutController::class, 'logout'])->name('logout.perform');
    });
});
