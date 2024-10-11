<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleDriveController;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::get('google-drive/connect', [GoogleDriveController::class, 'connect'])->name('google.drive.connect');
    Route::get('google-drive/callback', [GoogleDriveController::class, 'callback'])->name('google.drive.callback');
    Route::get('google-drive/files', [GoogleDriveController::class, 'listFiles'])->name('google.drive.files');

    Route::get('/google-drive/folders', [GoogleDriveController::class, 'getGoogleDriveFolders'])->name('google.drive.folders');
    Route::post('/store-selected-images', [GoogleDriveController::class, 'storeSelectedImages'])->name('store.selected.images');
    Route::get('/recent-images', [GoogleDriveController::class, 'getRecentImages'])->name('recent.images');
    Route::post('/google-drive/disconnect', [GoogleDriveController::class, 'removeGoogleDriveConnection'])
    ->name('google.drive.disconnect');
    Route::post('/download-image', [GoogleDriveController::class, 'downloadImage'])->name('download.image');
    Route::get('/google-drive/refresh-token', [GoogleDriveController::class, 'refreshToken'])
    ->name('google.drive.refresh.token');
    // Route::get('/get-expiry', [GoogleDriveController::class, 'useGoogleDriveApi']);
    // Route::get('/api/validate-access-token', [GoogleDriveController::class, 'validateAccessToken']);
});

require __DIR__.'/auth.php';
