<?php

use App\Http\Controllers\pdf\PdfWorkController;
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
Route::get('/', function () {
    return response()->json(['hola' => 'mundo']);
});

Route::middleware(['api'])->prefix('pdf')->name('pdf.')->group(function () {

    Route::get('status/{pdfWork}', [PdfWorkController::class, 'status'])->name('status');

    Route::post('creator', [PdfWorkController::class, 'creator'])->name('creator');

});
