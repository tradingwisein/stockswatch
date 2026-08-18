<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FyersController;
use App\Http\Controllers\UpstoxController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/fyers/login', [FyersController::class, 'login'])
    ->name('fyers.login');

Route::get('/fyers/callback', [FyersController::class, 'callback'])
    ->name('fyers.callback');
    
Route::get('/fyers/profile', [FyersController::class, 'profile']);

Route::get('/fyers/quote', [FyersController::class, 'quote']);

Route::get('/fyers/quote/{symbol}', [FyersController::class, 'getQuote']);


Route::get('/upstox/login', [UpstoxController::class, 'login'])
    ->name('upstox.login');

Route::get('/upstox/callback', [UpstoxController::class, 'callback'])
    ->name('upstox.callback');

Route::get('/upstox/history', [UpstoxController::class, 'history']);

Route::get('/upstox/profile', [UpstoxController::class, 'profile']);

Route::get('/upstox/stock-details', [
    UpstoxController::class,
    'stockDetails'
]);