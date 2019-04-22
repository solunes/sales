<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
Route::get('productos', 'ProcessController@getProducts');
Route::get('producto/{slug}', 'ProcessController@findProduct');

Route::group(['prefix'=>'process'], function(){

    // Rutas para AJAX
    Route::get('/calculate-shipping/{shipping_id}/{city_id}/{weight}', 'ProcessController@getCalculateShipping');
    // Rutas para añadir y borrar del carro de compras
    Route::get('/add-cart-item/{id}', 'ProcessController@getAddCartItem');
    Route::post('/add-cart-item', 'ProcessController@postAddCartItem');
    Route::get('/delete-cart-item/{id}', 'ProcessController@getDeleteCartItem');
    // Rutas para Comprar Ahora
    Route::get('/comprar-ahora/{slug}', 'ProcessController@getBuyNow');
    Route::post('/buy-now', 'ProcessController@postBuyNow');
    // Rutas para Confirmar Compra
    Route::get('/confirmar-compra/{type}/{cart_id?}', 'ProcessController@getCheckCart');
    Route::post('/update-cart', 'ProcessController@postUpdateCart');
    // Rutas para Finalizar Compra
    Route::get('/finalizar-compra/{cart_id?}', 'ProcessController@getFinishSale');
    Route::post('/finish-sale', 'ProcessController@postFinishSale');
    // Rutas para Revisar Compra Finalizada
    Route::get('/sale/{id}', 'ProcessController@getSale')->middleware('auth');
    Route::post('/sale-update-nit', 'ProcessController@postSaleUpdateNit')->middleware('auth');
    Route::post('/sp-bank-deposit', 'ProcessController@postSpBankDeposit');
    Route::get('/test-success-sale-email/{sale_id}', 'ProcessController@getTestSuccessSale')->middleware('auth');
});
Route::group(['prefix'=>'account'], function(){
    // Rutas para Mi Historial y Carro Pendiente
    Route::get('/sales-history/{token}', 'ProcessController@getMySalesHistory')->middleware('auth');
    Route::get('/my-pending-carts/{token}', 'ProcessController@getMyPendingCarts')->middleware('auth');
});