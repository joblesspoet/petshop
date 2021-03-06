<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Product\ProductController;
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

    // admin routes.
    Route::prefix('admin')->group(function () {
        Route::post('login', [AdminAuthController::class, 'login'])->name('login');
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::post('forgot', [AdminAuthController::class, 'forgot'])->name('forgot.password');
        Route::post('reset/password', [AdminAuthController::class, 'resetPassword'])->name('forgot.password');

        Route::middleware(['auth:api', 'admin'])->group(function () {
            Route::get('user-listing', [AdminController::class, 'index'])->name('admin.user.listing');
            Route::post('create', [AdminController::class, 'store'])->name('admin.create');
        });
    });

    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('forgot', [AuthController::class, 'forgot'])->name('forgot.password');
    Route::post('reset/password', [AuthController::class, 'resetPassword'])->name('forgot.password');

    Route::get('categories', [CategoryController::class, 'index'])->name('categories');

    Route::middleware('auth:api')->prefix('category')->group(function () {
        Route::post('create', [CategoryController::class, 'store'])->name('category.create');
        Route::get('{uuid}', [CategoryController::class, 'show'])->name('category.show');
        Route::match(['put', 'patch'], '{uuid}', [CategoryController::class, 'update'])->name('category.update');
        Route::delete('{uuid}', [CategoryController::class, 'destroy'])->name('category.delete');
    });

    Route::get('products', [ProductController::class, 'index'])->name('products');

    Route::middleware('auth:api')->prefix('product')->group(function () {
        Route::post('create', [ProductController::class, 'store'])->name('product.create');
        Route::get('{uuid}', [ProductController::class, 'show'])->name('product.show');
        Route::match(['put', 'patch'], '{uuid}', [ProductController::class, 'update'])->name('product.update');
        Route::delete('{uuid}', [ProductController::class, 'destroy'])->name('product.delete');
    });

    // common routes like user profile
    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'aboutMe'])->name('about.me');
    });
});
