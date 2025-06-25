<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\ViewController;
use App\Http\Controllers\PropertiesController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvSetting;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RpcppeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\PropertyTypeController;
use App\Http\Controllers\PropertyTypeLowController;
use App\Http\Controllers\PropertyTypeHighController; 
use App\Http\Controllers\PropertyTypeIntController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\EnduserController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\TechController;
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
        return view('login');
    });

    //Login
    Route::get('/login',[LoginController::class,'getLogin'])->name('getLogin');
    Route::post('/login',[LoginController::class,'postLogin'])->name('postLogin');

    Route::get('/qr-check', [PropertiesController::class, 'geneCheck'])->name('gene-check');
    Route::get('/gene-qr', [InventoryController::class, 'geneQr'])->name('gene-qr');

    Route::middleware(['android'])->group(function () {
        Route::get('/app-login',[UserController::class,'appLogin'])->name('appLogin');
         
        Route::get('/instat', [PropertiesController::class, 'propertiesStat'])->name('propertiesStat');
        Route::get('/instat-update', [PropertiesController::class, 'propertiesStatUp'])->name('propertiesStatUp');
        Route::get('/checkInv', [InventoryController::class, 'checkInv'])->name('check-inv');
    });
    
    Route::prefix('/technician')->group(function () {
        Route::get('/repair', [TechController::class, 'repairRead'])->name('repairRead');
        Route::get('/repair-create', [TechController::class, 'repairCreate'])->name('repairCreate');
        Route::get('/editEssue/{id}', [TechController::class, 'editEssue'])->name('editEssue.edit');
        Route::get('/issueInsert/{id}', [TechController::class, 'issueInsert'])->name('issueInsert.insert');
        Route::post('/issueUpdate/{id}', [TechController::class, 'issueUpdate'])->name('issueUpdate');
        Route::post('/issueInsertion', [TechController::class, 'issueInsertion'])->name('issueInsertion');
    });

    //Middleware
    Route::group(['middleware'=>['login_auth']],function(){
    Route::get('/dashboard',[MasterController::class,'dashboard'])->name('dashboard');
        
    //View
    Route::prefix('/view')->group(function () {
        // Route::get('/', [ViewController::class, 'index'])->name('manage-index');
        Route::prefix('/property')->group(function () {
            Route::post('list/create', [PropertyTypeController::class, 'ppeCreate'])->name('ppeCreate');
            Route::get('/listPPE', [PropertyTypeController::class, 'ppeRead'])->name('ppeRead');
            Route::get('list/edit/{id}', [PropertyTypeController::class, 'ppeEdit'])->name('ppeEdit');
            Route::post('list/update', [PropertyTypeController::class, 'ppeUpdate'])->name('ppeUpdate');
            Route::get('list/delete/{id}', [PropertyTypeController::class, 'ppeDelete'])->name('ppeDelete');

            Route::post('listLV/create', [PropertyTypeLowController::class, 'lvCreate'])->name('lvCreate');
            Route::get('/listLV', [PropertyTypeLowController::class, 'lvRead'])->name('lvRead');
            Route::get('list/{id}/LVedit', [PropertyTypeLowController::class, 'lvEdit'])->name('lvEdit');
            Route::post('listLV/update', [PropertyTypeLowController::class, 'lvUpdate'])->name('lvUpdate');
            Route::get('listLV/delete/{id}', [PropertyTypeLowController::class, 'lvDelete'])->name('lvDelete');

            Route::post('listHV/create', [PropertyTypeHighController::class, 'hvCreate'])->name('hvCreate');
            Route::get('/listHV', [PropertyTypeHighController::class, 'hvRead'])->name('hvRead');
            Route::get('list/{id}/HVedit', [PropertyTypeHighController::class, 'hvEdit'])->name('hvEdit');
            Route::post('listHV/update', [PropertyTypeHighController::class, 'hvUpdate'])->name('hvUpdate');
            Route::get('listHV/delete/{id}', [PropertyTypeHighController::class, 'hvDelete'])->name('hvDelete');

            Route::post('listINT/create', [PropertyTypeIntController::class, 'intCreate'])->name('intCreate');
            Route::get('/listINT', [PropertyTypeIntController::class, 'intRead'])->name('intRead');
            Route::get('list/{id}/INTedit', [PropertyTypeIntController::class, 'intEdit'])->name('intEdit');
            Route::post('listINT/update', [PropertyTypeIntController::class, 'intUpdate'])->name('intUpdate');
            Route::get('listINT/delete/{id}', [PropertyTypeIntController::class, 'intDelete'])->name('intDelete');
        });
        

        Route::prefix('/unit')->group(function () {
            Route::get('/list', [UnitController::class, 'unitRead'])->name('unitRead');
            Route::post('/list', [UnitController::class, 'unitCreate'])->name('unitCreate');
            Route::get('list/edit/{id}', [UnitController::class, 'unitEdit'])->name('unitEdit');
            Route::post('list/update', [UnitController::class, 'unitUpdate'])->name('unitUpdate');
            Route::get('list/delete/{id}', [UnitController::class, 'unitDelete'])->name('unitDelete');
        });

        Route::prefix('/item')->group(function () {
            Route::get('/list', [ItemController::class, 'itemRead'])->name('itemRead');
            Route::post('/list', [ItemController::class, 'itemCreate'])->name('itemCreate');
            Route::get('list/edit/{id}', [ItemController::class, 'itemEdit'])->name('itemEdit');
            Route::post('list/update', [ItemController::class, 'itemUpdate'])->name('itemUpdate');
            Route::get('list/delete/{id}', [ItemController::class, 'itemDelete'])->name('itemDelete'); 
        });

        Route::prefix('/office')->group(function () {
            Route::get('/list', [OfficeController::class, 'officeRead'])->name('officeRead');
            Route::post('/list', [OfficeController::class, 'officeCreate'])->name('officeCreate');
            Route::get('list/edit/{id}', [OfficeController::class, 'officeEdit'])->name('officeEdit');
            Route::post('list/update', [OfficeController::class, 'officeUpdate'])->name('officeUpdate');
            Route::get('list/delete/{id}', [OfficeController::class, 'officeDelete'])->name('officeDelete');
        });

        Route::prefix('/accntperson')->group(function () {
            Route::get('/list', [EnduserController::class, 'accountableRead'])->name('accountableRead');
            Route::post('/list', [EnduserController::class, 'accountableCreate'])->name('accountableCreate');
            Route::get('list/edit/{id}', [EnduserController::class, 'accountableEdit'])->name('accountableEdit');
            Route::post('list/update', [EnduserController::class, 'accountableUpdate'])->name('accountableUpdate');
            Route::get('list/delete/{id}', [EnduserController::class, 'accountableDelete'])->name('accountableDelete');
        });
    });

    //purchases
    Route::prefix('/purchases')->group(function() {
        Route::get('/list/all', [PurchaseController::class, 'purchaseREAD'])->name('purchaseREAD');
        Route::post('/list/add', [PurchaseController::class, 'purchaseCreate'])->name('purchaseCreate');
        Route::get('/list/delete/{id}', [PurchaseController::class, 'purchaseRelDel'])->name('purchaseRelDel');
        
        Route::get('/list/purchase-get/{id}', [PurchaseController::class, 'purchaseReleaseGet'])->name('purchaseReleaseGet');
        Route::post('/list/purchase-post', [PurchaseController::class, 'purchaseReleasePost'])->name('purchaseReleasePost');
        Route::get('/list/ajax', [PurchaseController::class, 'getPurchase'])->name('getPurchase');
    });

    //properties
    Route::prefix('/properties')->group(function () {
        Route::get('/list', [PropertiesController::class, 'propertiesRead'])->name('propertiesRead');
        Route::get('/list/ajax', [PropertiesController::class, 'getProperties'])->name('getProperties');
        Route::get('/list/ppe', [PropertiesController::class, 'propertiesppeRead'])->name('propertiesppeRead');
        Route::get('/list/high', [PropertiesController::class, 'propertieshighRead'])->name('propertieshighRead');
        Route::get('/list/low', [PropertiesController::class, 'propertieslowRead'])->name('propertieslowRead');
        Route::get('/list/intangible', [PropertiesController::class, 'propertiesintangibleRead'])->name('propertiesintangibleRead');
        
        Route::get('/return-slip/{id}', [PropertiesController::class, 'returnSlip'])->name('returnSlip');

        Route::post('/list/add', [PropertiesController::class, 'propertiesCreate'])->name('propertiesCreate');
        Route::get('/list/edit/{id}', [PropertiesController::class, 'propertiesEdit'])->name('propertiesEdit');
        Route::post('/list/update', [PropertiesController::class, 'propertiesUpdate'])->name('propertiesUpdate');
        Route::get('/list/cat/{id}/{mode}', [PropertiesController::class, 'propertiesCat'])->name('propertiesCat');
        Route::get('/list/prnt/{id}', [PropertiesController::class, 'propertiesPrntSticker'])->name('propertiesPrntSticker');
        Route::get('/list/delete/{id}', [PropertiesController::class, 'propertiesDelete'])->name('propertiesDelete'); 
        
        Route::post('/end-user/update', [PropertiesController::class, 'enduserUpdate'])->name('enduserUpdate');
        Route::get('/list/sticker', [PropertiesController::class, 'propertiesStickerTemplate'])->name('propertiesStickerTemplate');
        Route::get('/list/sticker/pdf', [PropertiesController::class, 'propertiesStickerTemplatePDF'])->name('propertiesStickerTemplatePDF');
    });
    
    //inventory
    Route::prefix('/inventory')->group(function () {
        Route::get('/list', [InventoryController::class, 'inventoryRead'])->name('inventoryRead');
        Route::get('/view/{id}', [InventoryController::class, 'inventoryView'])->name('inventoryView');
        Route::get('/list/ajax', [InventoryController::class, 'getInventory'])->name('getInventory');
        Route::post('/inventory/start', [InventoryController::class, 'startInventory'])->name('startInventory');
    });
     
    //Reports
    Route::prefix('/reports')->group(function () {
        Route::get('/rpcppe/option', [ReportsController::class, 'rpcppeOption'])->name('rpcppeOption');
        Route::get('/rpcppe/reports/rpcppe', [ReportsController::class, 'rpcppeOptionReportGen'])->name('rpcppeOptionReportGen');

        Route::get('/rpcsep/option', [ReportsController::class, 'rpcsepOption'])->name('rpcsepOption');
        Route::post('/rpcsep/reports/rpcsep', [ReportsController::class, 'rpcsepOptionReportGen'])->name('rpcsepOptionReportGen');

        Route::get('/ics/option', [ReportsController::class, 'icsOption'])->name('icsOption');
        Route::post('/ics/reports/ics', [ReportsController::class, 'icsOptionReportGen'])->name('icsOptionReportGen');
        Route::post('/ics/icsitemList', [ReportsController::class, 'icsgenOption'])->name('icsgenOption');

        Route::get('/unserviceable-form', [ReportsController::class, 'unserviceForm'])->name('unserviceForm');
        Route::post('/unserviceable-report', [ReportsController::class, 'unserviceReport'])->name('unserviceReport');
        
        Route::get('/par/option', [ReportsController::class, 'parOption'])->name('parOption');
        Route::post('/par/reports/par', [ReportsController::class, 'parOptionReportGen'])->name('parOptionReportGen');
        Route::post('/par/icsitemList', [ReportsController::class, 'pargenOption'])->name('pargenOption');
        
        Route::post('/par/itemList/unserv', [ReportsController::class, 'genOptionUnserv'])->name('genOptionUnserv');

        Route::get('/list/cat/{id}/{mode}', [PropertiesController::class, 'invCatIcsPar'])->name('invCatIcsPar');


        Route::post('/allgenoption', [ReportsController::class, 'allgenOption'])->name('allgenOption');
    });

    //Users
    Route::prefix('/users')->group(function () {
        Route::get('/list',[UserController::class,'userRead'])->name('userRead');
        Route::post('/list', [UserController::class, 'userCreate'])->name('userCreate');
        Route::get('list/edit/{id}', [UserController::class, 'userEdit'])->name('userEdit');
        Route::post('list/update', [UserController::class, 'userUpdate'])->name('userUpdate');
        Route::get('list/delete/{id}', [UserController::class, 'userDelete'])->name('userDelete');
    });

    //Settings
    Route::prefix('/settings')->group(function () {
        Route::get('/account-settings',[SettingsController::class,'user_settings'])->name('user_settings');
        Route::post('/account-settings/update',[SettingsController::class,'profileUpdate'])->name('profileUpdate');
        Route::post('/acccount-settings/updatePass',[SettingsController::class,'profilePassUpdate'])->name('profilePassUpdate');
        Route::get('/system-name',[SettingsController::class,'setting_list'])->name('setting_list');
        Route::post('/system-name',[SettingsController::class,'upload'])->name('upload');
    });
    
    //Logout
    Route::get('/logout',[MasterController::class,'logout'])->name('logout');
});
