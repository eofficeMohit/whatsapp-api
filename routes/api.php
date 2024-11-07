<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatroomController;
use App\Http\Controllers\MessageController;


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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });




Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->get('user', [AuthController::class, 'user']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/chatrooms', [ChatroomController::class, 'create']);
    Route::get('/chatrooms', [ChatroomController::class, 'index']);
    Route::post('/chatrooms/{chatroomId}/join', [ChatroomController::class, 'join']);
    Route::post('/chatrooms/{chatroomId}/leave', [ChatroomController::class, 'leave']);

    Route::post('/messages', [MessageController::class, 'send']);
    Route::get('/messages/{chatroomId}', [MessageController::class, 'list']);
});
