<?php

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

// api/users
Route::group(['prefix' => '/users'], function () {
    Route::get('/', [App\Http\Controllers\UserController::class, 'index']);
    Route::post('/login', [App\Http\Controllers\UserController::class,'login']);
    Route::post('/register', [App\Http\Controllers\UserController::class, 'store']);
    Route::post('/attachArticles', [App\Http\Controllers\UserController::class, 'attachArticles']);
    Route::post('/detachArticles', [App\Http\Controllers\UserController::class, 'detachArticles']);
    Route::post('/import', [App\Http\Controllers\UserController::class,'importUsers']);
    Route::get('/{id}', [App\Http\Controllers\UserController::class, 'get']);
    Route::put('/{id}', [App\Http\Controllers\UserController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\UserController::class, 'destroy']);
    Route::post('/role/{id}', [App\Http\Controllers\UserController::class, 'setRole']);
    Route::get('/payments/{id}', [App\Http\Controllers\UserController::class, 'payments']);
    Route::get('/articles/{id}', [App\Http\Controllers\UserController::class, 'articles']);
});

// api/articles
Route::group(['prefix' => '/articles'], function () {
    Route::get('/download', [App\Http\Controllers\ArticleController::class,'downloadDocument']);
    Route::get('/', [App\Http\Controllers\ArticleController::class, 'getAll']);
    Route::post('/', [App\Http\Controllers\ArticleController::class, 'save']);
    Route::get('/{id}', [App\Http\Controllers\ArticleController::class, 'search']);
    Route::put('/{id}', [App\Http\Controllers\ArticleController::class, 'refresh']);
    Route::delete('/{id}', [App\Http\Controllers\ArticleController::class, 'delete']);
});

// api/payments
Route::group(['prefix' => '/payments'], function () {
    Route::get('/', [App\Http\Controllers\PaymentController::class, 'index']);
    Route::post('/open', [App\Http\Controllers\PaymentController::class, 'open']);
    Route::post('/cancel/{id}', [App\Http\Controllers\PaymentController::class, 'cancel']);
    Route::post('/close/{id}', [App\Http\Controllers\PaymentController::class, 'close']);
    Route::get('/{id}', [App\Http\Controllers\PaymentController::class, 'show']);
    Route::put('/{id}', [App\Http\Controllers\PaymentController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\PaymentController::class, 'destroy']);
    Route::post('/status/{id}', [App\Http\Controllers\PaymentController::class, 'status']);
    Route::get('/getPrice/{id}', [App\Http\Controllers\PaymentController::class, 'getDataForPayment']);
});

// api/settings
Route::group(['prefix' => '/settings'], function () {
    Route::post('/rate', [App\Http\Controllers\SettingController::class, 'setRate']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
