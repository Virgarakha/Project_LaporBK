<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PengaduanController;
use App\Models\Pengaduan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function(){

    Route::prefix('auth')->group(function(){
        Route::post('register', [AuthController::class, 'register']);
        Route::post('verify', [AuthController::class, 'verifyOtp']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('resendOtp', [AuthController::class, 'resendOtp']);
    });

    Route::prefix('siswa')->group(function(){
        Route::get('pengaduan', [PengaduanController::class, 'index']);
        Route::get('pengaduan/{id}', [PengaduanController::class, 'showbyuser']);
        Route::post('pengaduan/create', [PengaduanController::class, 'store']);
        Route::put('pengaduan/update/{id}', [PengaduanController::class, 'update']);
        Route::delete('pengaduan/delete/{id}', [PengaduanController::class, 'destroy']);
    })->middleware('isSiswa');

    Route::prefix('guru')->group(function(){
        Route::get('pengaduan', [PengaduanController::class, 'getData']);
        Route::get('pengaduan/{id}', [PengaduanController::class, 'show']);
        Route::post('balasan/create', [PengaduanController::class, 'reply']);
        Route::put('balasan/update/{id}', [PengaduanController::class, 'editBalasan']);
        Route::delete('balasan/delete/{id}', [PengaduanController::class, 'hapusBalasan']);
        Route::put('pengaduan/status/{id}', [PengaduanController::class, 'setStatus']);
        Route::get('pengaduan/filter', [PengaduanController::class, 'filter']);
    })->middleware('isGuru');
});
