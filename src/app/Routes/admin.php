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

Route::group(['prefix'=>'admin'], function(){
    
    // Módulo de Reportes
    Route::get('create-manual-sale', 'CustomAdminController@getCreateManualSale');
    Route::post('create-manual-sale', 'CustomAdminController@postCreateManualSale');
    Route::get('create-manual-quotation', 'CustomAdminController@getCreateManualQuotation');
    Route::post('create-manual-quotation', 'CustomAdminController@postCreateManualQuotation');
    Route::get('create-sale-refund/{sale_id?}', 'CustomAdminController@getCreateSaleRefund');
    Route::post('create-sale-refund', 'CustomAdminController@postCreateSaleRefund');
    Route::get('sales-report', 'ReportController@getSalesSummary');
    Route::get('sales-detail-report', 'ReportController@getSalesDetail');

});