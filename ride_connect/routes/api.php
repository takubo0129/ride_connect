<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VehicleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| ここに書いたルートは全て "api" ミドルウェアグループに属します。
| アクセスは http://localhost:8000/api/... になります。
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 都道府県に紐づく市区町村を返す
// Prefecture → City の Ajax 用ルート
Route::get('/prefectures/{prefecture}/cities', [VehicleController::class, 'getCities']);
