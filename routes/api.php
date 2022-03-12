<?php

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

use App\Http\Controllers\SalController;
use App\Http\Controllers\BookstoreController;
use App\Http\Controllers\WebhookController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/schools', [SalController::class, 'getSchools']);

Route::get('/sal/{school}/{endpoint?}', [SalController::class, 'process']);

Route::get('/{endpoint?}', [BookstoreController::class, 'process']);
Route::post('/{endpoint?}', [BookstoreController::class, 'process']);


//Webhook

Route::get('/webhook/copies:updated', [WebhookController::class, 'onCopiesUpdated']);
Route::post('/webhook/copies:created', [WebhookController::class, 'onCopiesCreated']);

Route::post('/webhook/transferorders:updated', [WebhookController::class, 'onTransferOrdersUpdated']);
Route::post('/webhook/transferorders:created', [WebhookController::class, 'onTransferOrdersCreated']);
