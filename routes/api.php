<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\DepositController;
use App\Http\Controllers\Api\WastetypeController;
use App\Http\Controllers\Api\WithDrawalController;
use App\Http\Controllers\Api\ResetPasswordController;
use App\Http\Controllers\Api\SodakoController;
use App\Http\Controllers\Api\BalanceController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [ResetPasswordController::class, 'reset']);

    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:api')->group(function () {
    //User
    Route::get('/users', [AuthController::class, 'index'])->name('users.index');
    Route::get('/users/{id}', [AuthController::class, 'show'])->name('users.show');
    Route::post('/users', [AuthController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [AuthController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [AuthController::class, 'destroy'])->name('users.destroy');

    // Waste Type
    Route::get('/wastetypes', [WastetypeController::class, 'index'])->name('wastetypes.index');
    Route::post('/wastetypes', [WastetypeController::class, 'store'])->name('wastetypes.store');
    Route::get('/wastetypes/{id}', [WastetypeController::class, 'show'])->name('wastetypes.show');
    Route::put('/wastetypes/{id}', [WastetypeController::class, 'update'])->name('wastetypes.update');
    Route::delete('/wastetypes/{id}', [WastetypeController::class, 'destroy'])->name('wastetypes.destroy');

    // Deposit
    Route::get('/deposits', [DepositController::class, 'index'])->name('deposits.index');
    Route::post('/deposits', [DepositController::class, 'store'])->name('deposits.store');
    Route::get('/deposits/{id}', [DepositController::class, 'show'])->name('deposits.show');
    Route::put('/deposits/{id}', [DepositController::class, 'update'])->name('deposits.update');
    Route::delete('/deposits/{id}', [DepositController::class, 'destroy'])->name('deposits.destroy');

    // Withdrawal
    Route::get('/withdrawals', [WithDrawalController::class, 'index'])->name('withdrawals.index');
    Route::post('/withdrawals', [WithDrawalController::class, 'store'])->name('withdrawals.store');
    Route::get('/withdrawals/{id}', [WithDrawalController::class, 'show'])->name('withdrawals.show');
    Route::put('/withdrawals/{id}', [WithDrawalController::class, 'update'])->name('withdrawals.update');
    Route::delete('/withdrawals/{id}', [WithDrawalController::class, 'destroy'])->name('withdrawals.destroy');

    // Sodako
    Route::get('/sodakos', [SodakoController::class, 'index'])->name('sodakos.index');
    Route::post('/sodakos', [SodakoController::class, 'store'])->name('sodakos.store');
    Route::get('/sodakos/{id}', [SodakoController::class, 'show'])->name('sodakos.show');
    Route::put('/sodakos/{id}', [SodakoController::class, 'update'])->name('sodakos.update');
    Route::delete('/sodakos/{id}', [SodakoController::class, 'destroy'])->name('sodakos.destroy');


    // Balance
    Route::get('/balances/{id_user}', [BalanceController::class, 'saldoByUser'])->name('balances.saldoByUser');
    Route::get('/balances', [BalanceController::class, 'saldoAll'])->name('balances.saldoAll');

});