<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;
use App\Models\User;

Route::post('/tokens/create', function (Request $request) {
    $user = User::find(1);
    $token = $user->createToken('token-name');
 
    return ['token' => $token->plainTextToken];
});
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/orders', [OrderController::class, 'store']);
});
Route::get('/payment', [OrderController::class, 'payment']);
Route::get('/show-product/{product}', [OrderController::class, 'showProduct']);
Route::get('/show-top-products/{limit?}', [OrderController::class, 'showTopProducts']);
