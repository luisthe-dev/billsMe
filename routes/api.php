<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('user')->group(function () {

    Route::post('signup', [UserController::class, 'RegisterUser']);
    Route::post('verify', [UserController::class, 'VerifySignUpToken']);

    Route::post('login', [UserController::class, 'LoginUser']);

    Route::post('reset-password-otp', [UserController::class, 'requestResetOtp']);
    Route::post('reset-password', [UserController::class, 'resetPassword']);
});
