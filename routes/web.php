<?php

use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::get('/', [MessageController::class, 'index'])->name('users')->middleware('auth');
Route::get('/chat/{id}', [MessageController::class, 'chatForm'])->name('chat.form')->middleware('auth');
Route::post('/chat/{id}', [MessageController::class, 'sendMessage'])->name('chat.send')->middleware('auth');
Route::get('/chat/fetch/{receiverId}', [MessageController::class, 'fetchMessages'])->name('chat.fetch')->middleware('auth');

require __DIR__ . '/auth.php';