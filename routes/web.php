<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


// Route::get('/chat', function () {
//     return view('chat'); // This will render the chat.blade.php view
// });

Route::get('/chat', [MessageController::class, 'show']);

