<?php

use App\Http\Controllers\AttributeController;
use App\Http\Controllers\Config\collectionOfPersonalController;
use App\Http\Controllers\Config\DeleteVendorApiController;
use App\Http\Controllers\Popup\demandController;
use App\Http\Controllers\Popup\fiscalizationController;
use App\Http\Controllers\Popup\salesreturnController;
use App\Http\Controllers\Popup\sendController;
use App\Http\Controllers\vendor\vendorEndpoint;
use App\Http\Controllers\Web\AutomationController;
use App\Http\Controllers\Web\cash_operationController;
use App\Http\Controllers\Web\changeController;
use App\Http\Controllers\Web\close_z_shiftController;
use App\Http\Controllers\Web\get_shift_reportController;
use App\Http\Controllers\Web\indexController;
use App\Http\Controllers\Web\Setting\AccessController;
use App\Http\Controllers\Web\Setting\documentController;
use App\Http\Controllers\Web\Setting\errorSettingController;
use App\Http\Controllers\Web\Setting\KassaController;
use App\Http\Controllers\Web\Setting\mainController;
use App\Http\Controllers\Web\v2\connectV2Controller;
use App\Http\Controllers\Web\v2\initController;
use App\Http\Controllers\Web\v2\kassaV2Controller;
use App\Http\Controllers\Widget\customerorderEditController;
use App\Http\Controllers\Widget\demandEditController;
use App\Http\Controllers\Widget\salesreturnEditController;
use Illuminate\Support\Facades\Route;


Route::group(["prefix" => "/api/moysklad/vendor/1.0/apps"], function () {
    Route::put('/{apps}/{accountId}', [vendorEndpoint::class, 'put']);
    Route::delete('/{apps}/{accountId}', [vendorEndpoint::class, 'delete']);
});
Route::get('setAttributes/{accountId}/{tokenMs}', [AttributeController::class, 'setAllAttributesVendor']);



Route::get('/', [indexController::class, 'index']);
Route::get('/{accountId}/', [indexController::class, 'indexShow'])->name('main');

Route::get('/Setting/error/{accountId}', [errorSettingController::class, 'getError'])->name('errorSetting');



Route::group(["prefix" => "Setting"], function () {
    Route::get('/createAuthToken/{accountId}', [mainController::class, 'getMain']);
    Route::post('/createAuthToken/{accountId}', [mainController::class, 'postMain']);

    Route::get('/Kassa/{accountId}', [KassaController::class, 'getKassa'])->name('getKassa');
    Route::post('/Kassa/{accountId}', [KassaController::class, 'postKassa']);

    Route::get('/Document/{accountId}', [documentController::class, 'getDocument'])->name('getDocument');
    Route::post('/Document/{accountId}', [documentController::class, 'postDocument']);


    Route::get('/Worker/{accountId}', [AccessController::class, 'getWorker'])->name('getWorker');
    Route::post('/Worker/{accountId}', [AccessController::class, 'postWorker']);


    Route::get('/Automation/{accountId}', [AutomationController::class, 'getAutomation'])->name('getAutomation');
    Route::post('/Automation/{accountId}', [AutomationController::class, 'postAutomation']);
});






Route::get('/kassa/change/{accountId}', [changeController::class, 'getChange']);

Route::get('/operation/cash_operation/{accountId}', [cash_operationController::class, 'getCash']);
Route::post('/operation/cash_operation/{accountId}', [cash_operationController::class, 'postCash']);


Route::get('/operation/close_z_shift/{accountId}', [close_z_shiftController::class, 'getZShift']);
Route::post('/operation/close_z_shift/{accountId}', [close_z_shiftController::class, 'postZShift']);
Route::get('/operation/close_z_shift/print/{accountId}', [close_z_shiftController::class, 'printZShift']);

Route::get('/kassa/get_shift_report/{accountId}', [get_shift_reportController::class, 'getXShift']);
Route::post('/kassa/get_shift_report/{accountId}', [get_shift_reportController::class, 'postXShift']);
Route::get('/kassa/get_shift_report/print/{accountId}', [get_shift_reportController::class, 'printXShift']);
Route::get('/kassa/get_shift_report/info/{accountId}', [get_shift_reportController::class, 'infoXShift']);

Route::get('/get/createAuthToken/{accountId}', [mainController::class, 'createAuthToken']);


Route::get('/widget/InfoAttributes/', [indexController::class, 'widgetInfoAttributes']);

Route::get('/widget/customerorder', [customerorderEditController::class, 'customerorder']);
Route::get('/widget/demand', [demandEditController::class, 'demand']);
Route::get('/widget/salesreturn', [salesreturnEditController::class, 'salesreturn']);



Route::post('/Popup/DevRequest/send', [sendController::class, 'DevRequest']);




Route::post('/Popup/CreateRequest/send', [sendController::class, 'SendCreateRequest']);

Route::post('/Popup/Request/send', [sendController::class, 'SendRequest']);
Route::post('/Popup/Request/closeShift', [sendController::class, 'RequestClose']);


Route::get('/Popup/customerorder', [fiscalizationController::class, 'fiscalizationPopup']);
Route::get('/Popup/customerorder/show', [fiscalizationController::class, 'ShowFiscalizationPopup']);
Route::post('/Popup/customerorder/send', [fiscalizationController::class, 'SendFiscalizationPopup']);
Route::get('/Popup/customerorder/print/{accountId}', [fiscalizationController::class, 'printFiscalizationPopup']);

Route::get('/Popup/demand', [demandController::class, 'demandPopup']);
Route::get('/Popup/demand/show', [demandController::class, 'ShowDemandPopup']);
Route::post('/Popup/demand/send', [demandController::class, 'SendDemandPopup']);
Route::get('/Popup/demand/print/{accountId}', [demandController::class, 'printDemandPopup']);

Route::get('/Popup/salesreturn', [salesreturnController::class, 'salesreturnPopup']);
Route::get('/Popup/salesreturn/show', [salesreturnController::class, 'ShowSalesreturnPopup']);
Route::post('/Popup/salesreturn/send', [salesreturnController::class, 'SendSalesreturnPopup']);
Route::get('/Popup/salesreturn/print/{accountId}', [salesreturnController::class, 'printSalesreturnPopup']);



/**V2 route */
Route::group(["prefix" => "Setting"], function () {
    Route::get('/initSetting/{accountId}', [initController::class, 'initSetting'])->name('init');

    Route::get('/createSetting/{accountId}', [initController::class, 'create'])->name('create');
    Route::post('/createSetting/{accountId}', [initController::class, 'post']);


    Route::group(["prefix" => "Update"], function () {
        Route::get('connect/{uid}/{accountId}', [connectV2Controller::class, 'view'])->name('connect');
        Route::post('connect/{uid}/{accountId}', [connectV2Controller::class, 'save']);


        Route::get('kassa/{uid}/{accountId}', [kassaV2Controller::class, 'view'])->name('kassa');
        Route::post('kassa/{uid}/{accountId}', [kassaV2Controller::class, 'save']);
    });

});




/*//для админа
Route::get('/web/getPersonalInformation/', [collectionOfPersonalController::class, 'getPersonal']);
Route::get('/collectionOfPersonalInformation/{accountId}/', [collectionOfPersonalController::class, 'getCollection']);
Route::get('web/getInstallPersonalForID/{accountId}/', [collectionOfPersonalController::class, 'getInstallPersonalForID']);*/
