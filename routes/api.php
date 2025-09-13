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
        Route::get('pengaduan', [PengaduanController::class, 'index'])->middleware('isSiswa');
        Route::get('pengaduan/{id}', [PengaduanController::class, 'showbyuser'])->middleware('isSiswa');
        Route::post('pengaduan/create', [PengaduanController::class, 'store'])->middleware('isSiswa');
        Route::put('pengaduan/update/{id}', [PengaduanController::class, 'update'])->middleware('isSiswa');
        Route::delete('pengaduan/delete/{id}', [PengaduanController::class, 'destroy'])->middleware('isSiswa');
    });

    Route::prefix('guru')->group(function(){
        Route::get('pengaduan', [PengaduanController::class, 'getData'])->middleware('isGuru');
        Route::get('pengaduan/{id}', [PengaduanController::class, 'show'])->middleware('isGuru');
        Route::post('balasan/create/{id}', [PengaduanController::class, 'reply'])->middleware('isGuru');
        Route::put('balasan/update/{id}', [PengaduanController::class, 'editBalasan'])->middleware('isGuru');
        Route::delete('balasan/delete/{id}', [PengaduanController::class, 'hapusBalasan'])->middleware('isGuru');
        Route::put('pengaduan/status/{id}', [PengaduanController::class, 'setStatus'])->middleware('isGuru');
        Route::get('filter', [PengaduanController::class, 'filterPengaduan'])->middleware('isGuru');
    });
});
