<?php

use Illuminate\Support\Facades\Route;

use App\Models\TransferOrder;

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

use App\Http\Controllers\TransferOrderController;


Route::get('/bookstore-signage/{school_identifier}', function () {
    return view('bookstore-signage');
});


Route::get('{route?}', function () {
    return view('app');
})->where('route', '(.*)');
