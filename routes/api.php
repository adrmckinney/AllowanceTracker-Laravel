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
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserChoreController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPermissionController;

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

Route::get('/logout', [LogoutController::class, 'logout']);

Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm']);
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm']);
Route::post('password/reset', [ResetPasswordController::class, 'resetPassword'])->middleware('auth');

Route::get('/me/{id}', [UserController::class, 'getUser'])->middleware('auth');
Route::get('/users', [UserController::class, 'getUsers'])->middleware('auth');
Route::put('/user/update', [UserController::class, 'update'])->middleware('auth');
Route::delete('/user/{id}', [UserController::class, 'deleteUser'])->middleware('auth');

Route::get('/chore/{id}', [ChoreController::class, 'getChore'])->middleware('auth');
Route::get('/chores', [ChoreController::class, 'getChoreList'])->middleware('auth');
Route::post('/chore', [ChoreController::class, 'createChore'])->middleware('auth');
Route::put('/chore', [ChoreController::class, 'updateChore'])->middleware('auth');
Route::delete('/chore/{id}', [ChoreController::class, 'deleteChore'])->middleware('auth');

Route::get('/user-chore/{id}', [UserChoreController::class, 'getUserChore'])->middleware('auth');
Route::get("/user/{id}/chores", [UserChoreController::class, 'getUserChores'])->middleware('auth');
Route::get("/users/chore/{id}", [UserChoreController::class, 'getChoreUsers'])->middleware('auth');
Route::post('/user/{userId}/chore/{choreId}/add', [UserChoreController::class, 'addChoreToUser'])->middleware('auth');
Route::put('/user/chore/request', [UserChoreController::class, 'requestApproval'])->middleware('auth');
Route::put('/user/chore/approve', [UserChoreController::class, 'approveWork'])->middleware('auth');
Route::delete("/user-chore/{id}/remove", [UserChoreController::class, 'removeChoreFromUser'])->middleware('auth');

Route::get('/permission/{id}', [PermissionsController::class, 'getPermission'])->middleware('auth');
Route::get('/permissions', [PermissionsController::class, 'getPermissions'])->middleware('auth');
Route::post('/permission/create', [PermissionsController::class, 'createPermission'])->middleware('auth');
Route::put('/permission/update', [PermissionsController::class, 'updatePermission'])->middleware('auth');

Route::post('/user/permission/add', [UserPermissionController::class, 'addPermission'])->middleware('auth');
Route::put('/user/permission/update', [UserPermissionController::class, 'updatePermission'])->middleware('auth');

Route::get('/transaction/{id}', [TransactionController::class, 'getTransaction'])->middleware('auth');
Route::get('/transactions', [TransactionController::class, 'getTransactionsList'])->middleware('auth');
Route::post('/transaction/spend', [TransactionController::class, 'spendTransaction'])->middleware('auth');
Route::put('/transaction/approval', [TransactionController::class, 'approveTransaction'])->middleware('auth');
Route::put('/transaction/reject', [TransactionController::class, 'rejectTransaction'])->middleware('auth');

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
