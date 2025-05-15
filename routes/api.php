<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForecastController;

Route::get('/forecast', [ForecastController::class, 'fetch']);
Route::get('/generate-forecast', [ForecastController::class, 'generate']);
Route::get('/logs', [ForecastController::class, 'logs']);
Route::resource('/test', TestController::class);
