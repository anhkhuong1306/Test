<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TransactionController;
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

Route::get('/customers', [CustomerController::class, 'index'])->name('list-customers');
Route::get('/accounts', [AccountController::class, 'index'])->name('list-accounts');
Route::get('/account', [AccountController::class, 'detail'])->name('account-detail');
Route::get('/transactions', [TransactionController::class, 'index'])->name('list-transactions');
Route::get('/account/generate-account-number', [AccountController::class, 'generateAccountNumber'])->name('generate-account-number');
Route::post('/customers', [CustomerController::class, 'insert'])->name('new-customer');
Route::post('/accounts', [AccountController::class, 'insert'])->name('new-account');
Route::post('/account/transfer', [AccountController::class, 'transfer'])->name('transfer-money');
