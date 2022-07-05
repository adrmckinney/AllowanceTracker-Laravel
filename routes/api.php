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


// Route::post('/login', [LoginController::class, 'login'])->middleware('auth');

Route::group(['namespace' => 'App\Http\Controllers'], function () {
    /**
     * Home Routes
     */
    // Route::get('/', 'HomeController@index')->name('home.index');

    Route::group(['middleware' => ['guest']], function () {
        /**
         * Register Routes
         */
        Route::post('/register', [RegisterController::class, 'register']);

        /**
         * Login Routes
         */
        Route::post('/login', [LoginController::class, 'login']);
    });

    Route::group(['middleware' => ['auth']], function () {
        /**
         * Logout Routes
         */
        // Route::get('/logout', [LogoutController::class, 'logout'])->name('logout.perform');
        Route::get('/me/{id}', [UserController::class, 'getUser']);
        Route::get('/users', [UserController::class, 'getUsers']);
        Route::put('/user/update', [UserController::class, 'update']);
        Route::delete('/user/{id}', [UserController::class, 'deleteUser']);

        Route::get('/chore/{id}', [ChoreController::class, 'getChore']);
        Route::get('/chores', [ChoreController::class, 'getChoreList']);
        Route::post('/chore', [ChoreController::class, 'createChore']);
        Route::put('/chore', [ChoreController::class, 'updateChore']);
        Route::delete('/chore/{id}', [ChoreController::class, 'deleteChore']);

        Route::get('/user-chore/{id}', [UserChoreController::class, 'getUserChore']);
        Route::get("/user/{id}/chores", [UserChoreController::class, 'getUserChores']);
        Route::get("/users/chore/{id}", [UserChoreController::class, 'getChoreUsers']);
        Route::post('/user/{userId}/chore/{choreId}/add', [UserChoreController::class, 'addChoreToUser']);
        Route::put('/user/chore/request', [UserChoreController::class, 'requestApproval']);
        Route::put('/user/chore/approve', [UserChoreController::class, 'approveWork']);
        Route::delete("/user-chore/{id}/remove", [UserChoreController::class, 'removeChoreFromUser']);

        Route::get('/permission/{id}', [PermissionsController::class, 'getPermission']);
        Route::get('/permissions', [PermissionsController::class, 'getPermissions']);
        Route::post('/permission/create', [PermissionsController::class, 'createPermission']);
        Route::put('/permission/update', [PermissionsController::class, 'updatePermission']);

        Route::post('/user/permission/add', [UserPermissionController::class, 'addPermission']);
        Route::put('/user/permission/update', [UserPermissionController::class, 'updatePermission']);

        Route::get('/transaction/{id}', [TransactionController::class, 'getTransaction']);
        Route::get('/transactions', [TransactionController::class, 'getTransactionsList']);
        Route::post('/transaction/spend', [TransactionController::class, 'spendTransaction']);
        Route::put('/transaction/approval', [TransactionController::class, 'approveTransaction']);
        Route::put('/transaction/reject', [TransactionController::class, 'rejectTransaction']);
    });
});
