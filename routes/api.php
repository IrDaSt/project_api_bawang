<?php

use App\Http\Controllers\API\AlokasiKelasController;
use App\Http\Controllers\API\GuruController;
use App\Http\Controllers\API\KelasController;
use App\Http\Controllers\API\MataPelajaranController;
use App\Http\Controllers\API\MuridController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('role', [RoleController::class, 'index']);

Route::get('murid', [MuridController::class, 'index']);
Route::get('murid/{id}', [MuridController::class, 'show']);

Route::get('guru', [GuruController::class, 'index']);

Route::get('user', [UserController::class, 'index']);
Route::get('user/{id}', [UserController::class, 'show']);
Route::post('user/add', [UserController::class, 'store']);

Route::get('mata-pelajaran', [MataPelajaranController::class, 'index']);
Route::get('mata-pelajaran/{id}', [MataPelajaranController::class, 'show']);
Route::post('mata-pelajaran/add', [MataPelajaranController::class, 'store']);
Route::post('mata-pelajaran/update/{id}', [MataPelajaranController::class, 'update']);
Route::delete('mata-pelajaran/delete/{id}', [MataPelajaranController::class, 'destroy']);

Route::get('kelas', [KelasController::class, 'index']);
Route::get('kelas/{id}', [KelasController::class, 'show']);
Route::post('kelas/add', [KelasController::class, 'store']);
Route::post('kelas/update/{id}', [KelasController::class, 'update']);
Route::delete('kelas/delete/{id}', [KelasController::class, 'destroy']);

Route::get('alokasi-kelas', [AlokasiKelasController::class, 'index']);

Route::get('notification', [NotificationController::class, 'index']);
