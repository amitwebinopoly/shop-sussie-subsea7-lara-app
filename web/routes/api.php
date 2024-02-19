<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Middleware\EnsureFrontendShopAuth;

use App\Http\Controllers\AppController;
use App\Http\Controllers\FrontEndController;

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
    return "Hello API";
});

Route::get('/get_upload_logo',[ AppController::class,'get_upload_logo'])->middleware('shopify.auth');
Route::post('/upload_logo',[ AppController::class,'upload_logo'])->middleware('shopify.auth');
Route::post('/export_order',[ AppController::class,'export_order'])->middleware('shopify.auth');

Route::get('/get_shipping_zones',[ AppController::class,'get_shipping_zones'])->middleware('shopify.auth');
Route::get('/get_int_ship_address_list',[ AppController::class,'get_int_ship_address_list'])->middleware('shopify.auth');
Route::get('/get_int_ship_address/{id}',[ AppController::class,'get_int_ship_address'])->middleware('shopify.auth');
Route::post('/post_int_ship_address',[ AppController::class,'post_int_ship_address'])->middleware('shopify.auth');

Route::get('/approver-status/{shop}/{status}/{oid}',[ FrontEndController::class,'approver_status'])->name('approve_status_link');
Route::get('/approver-confirm/{shop}/{status}/{oid}',[ FrontEndController::class,'approver_confirm'])->name('approve_confirm_link');

//frontend
Route::middleware([EnsureFrontendShopAuth::class])->group(function () {
    Route::post('/post_abandoned_cart',[ FrontEndController::class,'post_abandoned_cart']);
    //Route::post('/get_data_first_checkout',[ FrontEndController::class,'get_data_first_checkout']);
    //Route::post('/grab_data_from_checkout',[ FrontEndController::class,'grab_data_from_checkout']);
    //Route::post('/get_checkout_data',[ FrontEndController::class,'get_checkout_data']);
    Route::post('/get_draft_order_data',[ FrontEndController::class,'get_draft_order_data']);
    Route::post('/store_abondoned_steps',[ FrontEndController::class,'store_abondoned_steps']);
    //Route::post('/apply_discount_in_checkout',[ FrontEndController::class,'apply_discount_in_checkout']);
    //Route::post('/remove_discount_in_checkout',[ FrontEndController::class,'remove_discount_in_checkout']);
    Route::post('/process_checkout',[ FrontEndController::class,'process_checkout']);
});