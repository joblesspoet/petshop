<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Category\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::group([
    'prefix' => 'v1'
], function ($router) {

    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('forgot', [AuthController::class, 'forgot'])->name('forgot.password');
    Route::post('reset/password', [AuthController::class, 'resetPassword'])->name('forgot.password');

    Route::get('categories', [CategoryController::class, 'index'])->name('categories');

    Route::middleware('auth:api')->prefix('category')->group(function () {
        Route::post('create', [CategoryController::class, 'store'])->name('category.create');
        Route::get('{uuid}',[CategoryController::class, 'show'])->name('category.show');
        Route::match(['put', 'patch'], '{uuid}', [CategoryController::class, 'update'])->name('category.update');
        Route::delete('{uuid}', [CategoryController::class, 'store'])->name('category.delete');
    });
});
