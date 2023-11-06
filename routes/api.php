<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('users', [UserController::class, 'createUser']);

Route::post('login', [UserController::class, 'userLogin']);

Route::get('show', [UserController::class, 'showAllTransactionAndBalance']);

Route::get('deposit', [UserController::class, 'showAllDepositTransaction']);

Route::post('deposit', [UserController::class, 'depositBalance']);

Route::get('withdrawal', [UserController::class, 'showAllWithdrawalTransaction']);

Route::post('withdrawal', [UserController::class, 'withdrawBalance']);