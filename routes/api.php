<?php

use App\Http\Controllers\authController;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/auth',[authController::class,'auth']);
Route::get('/emploi', [authController::class, 'getEmploi']);
Route::get('/note', [authController::class, 'getNoteEtudiant']);
Route::get('/noteprof', [authController::class, 'getNoteProf']);
Route::post('/noteprof/save', [authController::class, 'saveNoteProf']);
//For admin
Route::post('/login', [authController::class, 'login']);
//For student
Route::post('/logins', [authController::class, 'logins']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/admin', [authController::class, 'admin']);
});