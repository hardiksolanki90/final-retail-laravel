<?php

use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\BankInformationController;
use App\Http\Controllers\Api\ChannelController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\CurrencyMasterController;
use App\Http\Controllers\Api\CustomerCategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\CustomerGroupController;
use App\Http\Controllers\Api\CustomerTypeController;
use App\Http\Controllers\Api\DebitNoteController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\DepotController;
use App\Http\Controllers\Api\DriverAndVanSwapingController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\ItemGroupController;
use App\Http\Controllers\Api\ItemUomController;
use App\Http\Controllers\Api\MerchandiserReplacementController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrganisationController;
use App\Http\Controllers\Api\OutletProductCodeController;
use App\Http\Controllers\Api\PaymentTermController;
use App\Http\Controllers\Api\ReasonTypeController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\SalesmanController;
use App\Http\Controllers\Api\SalesmanLoadController;
use App\Http\Controllers\Api\TaxRateController;
use App\Http\Controllers\Api\UserCreditLimitController;
use App\Http\Controllers\Api\VanCategoryController;
use App\Http\Controllers\Api\VanController;
use App\Http\Controllers\Api\VanTypeController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\ZoneController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('organisation/current', [OrganisationController::class, 'current']);
    Route::post('organisation/update', [OrganisationController::class, 'update']);

    Route::get('salesman/list', [SalesmanController::class, 'list']);
    Route::get('salesman/all', [SalesmanController::class, 'all']);
    Route::post('salesman/search', [SalesmanController::class, 'search']);
    Route::get('salesman/edit/{uuid}', [SalesmanController::class, 'show']);
    Route::post('salesman/add', [SalesmanController::class, 'store']);
    Route::post('salesman/edit/{uuid}', [SalesmanController::class, 'update']);
    Route::post('salesman/delete', [SalesmanController::class, 'destroy']);
    Route::post('salesman/bulk-action', [SalesmanController::class, 'bulkAction']);
    Route::get('salesman/{uuid}/sales', [SalesmanController::class, 'sales']);
    Route::get('salesman/{userId}/login-history', [SalesmanController::class, 'loginHistory']);

    Route::get('customer/list', [CustomerController::class, 'list']);
    Route::get('customer/all', [CustomerController::class, 'all']);
    Route::post('customer/search', [CustomerController::class, 'search']);
    Route::get('customer/edit/{uuid}', [CustomerController::class, 'show']);
    Route::post('customer/add', [CustomerController::class, 'store']);
    Route::post('customer/edit/{uuid}', [CustomerController::class, 'update']);
    Route::delete('customer/delete/{uuid}', [CustomerController::class, 'destroy']);
    Route::post('customer/bulk-action', [CustomerController::class, 'bulkAction']);
    Route::get('customer/salesman/{salesmanId}', [CustomerController::class, 'bySalesman']);
    Route::get('customer/{uuid}/sales', [CustomerController::class, 'sales']);

    Route::get('customer-type/all', [CustomerTypeController::class, 'all']);
    Route::get('customer-type/edit/{uuid}', [CustomerTypeController::class, 'show']);
    Route::post('customer-type/add', [CustomerTypeController::class, 'store']);
    Route::post('customer-type/edit/{uuid}', [CustomerTypeController::class, 'update']);
    Route::post('customer-type/delete', [CustomerTypeController::class, 'destroy']);

    Route::get('customer-category/all', [CustomerCategoryController::class, 'all']);
    Route::get('customer-category/edit/{uuid}', [CustomerCategoryController::class, 'show']);
    Route::post('customer-category/add', [CustomerCategoryController::class, 'store']);
    Route::post('customer-category/edit/{uuid}', [CustomerCategoryController::class, 'update']);
    Route::post('customer-category/delete', [CustomerCategoryController::class, 'destroy']);

    Route::get('customer-group/all', [CustomerGroupController::class, 'all']);
    Route::get('customer-group/edit/{uuid}', [CustomerGroupController::class, 'show']);
    Route::post('customer-group/add', [CustomerGroupController::class, 'store']);
    Route::post('customer-group/edit/{uuid}', [CustomerGroupController::class, 'update']);
    Route::post('customer-group/delete', [CustomerGroupController::class, 'destroy']);

    Route::get('channel/all', [ChannelController::class, 'all']);
    Route::get('channel/edit/{uuid}', [ChannelController::class, 'show']);
    Route::post('channel/add', [ChannelController::class, 'store']);
    Route::post('channel/edit/{uuid}', [ChannelController::class, 'update']);
    Route::post('channel/delete', [ChannelController::class, 'destroy']);

    Route::get('payment-term/all', [PaymentTermController::class, 'all']);
    Route::get('payment-term/edit/{uuid}', [PaymentTermController::class, 'show']);
    Route::post('payment-term/add', [PaymentTermController::class, 'store']);
    Route::post('payment-term/edit/{uuid}', [PaymentTermController::class, 'update']);
    Route::post('payment-term/delete', [PaymentTermController::class, 'destroy']);

    Route::get('route/list', [RouteController::class, 'list']);
    Route::get('route/all', [RouteController::class, 'all']);
    Route::get('route/edit/{uuid}', [RouteController::class, 'show']);
    Route::post('route/add', [RouteController::class, 'store']);
    Route::post('route/edit/{uuid}', [RouteController::class, 'update']);
    Route::post('route/delete', [RouteController::class, 'destroy']);
    Route::delete('route/delete/{uuid}', [RouteController::class, 'destroyByUuid']);

    Route::get('item/list', [ItemController::class, 'list']);
    Route::get('item/all', [ItemController::class, 'all']);
    Route::get('item/with-stock', [ItemController::class, 'withStock']);
    Route::post('item/search', [ItemController::class, 'search']);
    Route::get('item/edit/{uuid}', [ItemController::class, 'show']);
    Route::post('item/add', [ItemController::class, 'store']);
    Route::post('item/edit/{uuid}', [ItemController::class, 'update']);
    Route::post('item/delete', [ItemController::class, 'destroy']);
    Route::post('item/bulk-action', [ItemController::class, 'bulkAction']);

    // Settings masters — flat CRUD (all/show/store/update/destroy), Channel-style
    Route::get('tax-rate/list', [TaxRateController::class, 'list']);
    Route::get('tax-rate/all', [TaxRateController::class, 'all']);
    Route::get('tax-rate/edit/{uuid}', [TaxRateController::class, 'show']);
    Route::post('tax-rate/add', [TaxRateController::class, 'store']);
    Route::post('tax-rate/edit/{uuid}', [TaxRateController::class, 'update']);
    Route::post('tax-rate/delete', [TaxRateController::class, 'destroy']);

    Route::get('bank/list', [BankInformationController::class, 'list']);
    Route::get('bank/all', [BankInformationController::class, 'all']);
    Route::get('bank/edit/{uuid}', [BankInformationController::class, 'show']);
    Route::post('bank/add', [BankInformationController::class, 'store']);
    Route::post('bank/edit/{uuid}', [BankInformationController::class, 'update']);
    Route::post('bank/delete', [BankInformationController::class, 'destroy']);

    Route::get('country/list', [CountryController::class, 'list']);
    Route::get('country/all', [CountryController::class, 'all']);
    Route::get('country/edit/{uuid}', [CountryController::class, 'show']);
    Route::post('country/add', [CountryController::class, 'store']);
    Route::post('country/edit/{uuid}', [CountryController::class, 'update']);
    Route::post('country/delete', [CountryController::class, 'destroy']);

    Route::get('item-group/list', [ItemGroupController::class, 'list']);
    Route::get('item-group/all', [ItemGroupController::class, 'all']);
    Route::get('item-group/edit/{uuid}', [ItemGroupController::class, 'show']);
    Route::post('item-group/add', [ItemGroupController::class, 'store']);
    Route::post('item-group/edit/{uuid}', [ItemGroupController::class, 'update']);
    Route::post('item-group/delete', [ItemGroupController::class, 'destroy']);

    Route::get('item-uom/list', [ItemUomController::class, 'list']);
    Route::get('item-uom/all', [ItemUomController::class, 'all']);
    Route::get('item-uom/edit/{uuid}', [ItemUomController::class, 'show']);
    Route::post('item-uom/add', [ItemUomController::class, 'store']);
    Route::post('item-uom/edit/{uuid}', [ItemUomController::class, 'update']);
    Route::post('item-uom/delete', [ItemUomController::class, 'destroy']);

    Route::get('reason-type/list', [ReasonTypeController::class, 'list']);
    Route::get('reason-type/all', [ReasonTypeController::class, 'all']);
    Route::get('reason-type/edit/{uuid}', [ReasonTypeController::class, 'show']);
    Route::post('reason-type/add', [ReasonTypeController::class, 'store']);
    Route::post('reason-type/edit/{uuid}', [ReasonTypeController::class, 'update']);
    Route::post('reason-type/delete', [ReasonTypeController::class, 'destroy']);

    Route::get('outlet-product-code/list', [OutletProductCodeController::class, 'list']);
    Route::get('outlet-product-code/all', [OutletProductCodeController::class, 'all']);
    Route::get('outlet-product-code/edit/{uuid}', [OutletProductCodeController::class, 'show']);
    Route::post('outlet-product-code/add', [OutletProductCodeController::class, 'store']);
    Route::post('outlet-product-code/edit/{uuid}', [OutletProductCodeController::class, 'update']);
    Route::post('outlet-product-code/delete', [OutletProductCodeController::class, 'destroy']);

    Route::get('zone/list', [ZoneController::class, 'list']);
    Route::get('zone/all', [ZoneController::class, 'all']);
    Route::get('zone/edit/{uuid}', [ZoneController::class, 'show']);
    Route::post('zone/add', [ZoneController::class, 'store']);
    Route::post('zone/edit/{uuid}', [ZoneController::class, 'update']);
    Route::post('zone/delete', [ZoneController::class, 'destroy']);

    Route::get('currency/list', [CurrencyController::class, 'list']);
    Route::get('currency/all', [CurrencyController::class, 'all']);
    Route::get('currency/edit/{uuid}', [CurrencyController::class, 'show']);
    Route::post('currency/add', [CurrencyController::class, 'store']);
    Route::post('currency/edit/{uuid}', [CurrencyController::class, 'update']);
    Route::post('currency/delete', [CurrencyController::class, 'destroy']);

    Route::get('currency-master/all', [CurrencyMasterController::class, 'all']);

    Route::get('region/list', [RegionController::class, 'list']);
    Route::get('region/all', [RegionController::class, 'all']);
    Route::get('region/edit/{uuid}', [RegionController::class, 'show']);
    Route::post('region/add', [RegionController::class, 'store']);
    Route::post('region/edit/{uuid}', [RegionController::class, 'update']);
    Route::post('region/delete', [RegionController::class, 'destroy']);

    Route::get('area/list', [AreaController::class, 'list']);
    Route::get('area/all', [AreaController::class, 'all']);
    Route::get('area/edit/{uuid}', [AreaController::class, 'show']);
    Route::post('area/add', [AreaController::class, 'store']);
    Route::post('area/edit/{uuid}', [AreaController::class, 'update']);
    Route::post('area/delete', [AreaController::class, 'destroy']);

    Route::get('depot/list', [DepotController::class, 'list']);
    Route::get('depot/all', [DepotController::class, 'all']);
    Route::get('depot/edit/{uuid}', [DepotController::class, 'show']);
    Route::post('depot/add', [DepotController::class, 'store']);
    Route::post('depot/edit/{uuid}', [DepotController::class, 'update']);
    Route::post('depot/delete', [DepotController::class, 'destroy']);

    Route::get('van-type/all', [VanTypeController::class, 'all']);
    Route::get('van-category/all', [VanCategoryController::class, 'all']);

    Route::get('user-credit-limit/list', [UserCreditLimitController::class, 'list']);
    Route::get('user-credit-limit/all', [UserCreditLimitController::class, 'all']);
    Route::get('user-credit-limit/edit/{uuid}', [UserCreditLimitController::class, 'show']);
    Route::post('user-credit-limit/add', [UserCreditLimitController::class, 'store']);
    Route::post('user-credit-limit/edit/{uuid}', [UserCreditLimitController::class, 'update']);
    Route::post('user-credit-limit/delete', [UserCreditLimitController::class, 'destroy']);

    // Settings masters — paginated CRUD (list/all/show/store/update/destroy/destroyByUuid), Route-style
    Route::get('van/list', [VanController::class, 'list']);
    Route::get('van/all', [VanController::class, 'all']);
    Route::get('van/edit/{uuid}', [VanController::class, 'show']);
    Route::post('van/add', [VanController::class, 'store']);
    Route::post('van/edit/{uuid}', [VanController::class, 'update']);
    Route::post('van/delete', [VanController::class, 'destroy']);
    Route::delete('van/delete/{uuid}', [VanController::class, 'destroyByUuid']);

    Route::get('warehouse/list', [WarehouseController::class, 'list']);
    Route::get('warehouse/all', [WarehouseController::class, 'all']);
    Route::get('warehouse/edit/{uuid}', [WarehouseController::class, 'show']);
    Route::post('warehouse/add', [WarehouseController::class, 'store']);
    Route::post('warehouse/edit/{uuid}', [WarehouseController::class, 'update']);
    Route::post('warehouse/delete', [WarehouseController::class, 'destroy']);
    Route::delete('warehouse/delete/{uuid}', [WarehouseController::class, 'destroyByUuid']);

    Route::get('merchandiser-replacement/list', [MerchandiserReplacementController::class, 'list']);
    Route::get('merchandiser-replacement/all', [MerchandiserReplacementController::class, 'all']);
    Route::get('merchandiser-replacement/edit/{uuid}', [MerchandiserReplacementController::class, 'show']);
    Route::post('merchandiser-replacement/add', [MerchandiserReplacementController::class, 'store']);
    Route::post('merchandiser-replacement/edit/{uuid}', [MerchandiserReplacementController::class, 'update']);
    Route::post('merchandiser-replacement/delete', [MerchandiserReplacementController::class, 'destroy']);
    Route::delete('merchandiser-replacement/delete/{uuid}', [MerchandiserReplacementController::class, 'destroyByUuid']);

    Route::get('driver-replacement/list', [DriverAndVanSwapingController::class, 'list']);
    Route::get('driver-replacement/all', [DriverAndVanSwapingController::class, 'all']);
    Route::get('driver-replacement/edit/{uuid}', [DriverAndVanSwapingController::class, 'show']);
    Route::post('driver-replacement/add', [DriverAndVanSwapingController::class, 'store']);
    Route::post('driver-replacement/edit/{uuid}', [DriverAndVanSwapingController::class, 'update']);
    Route::post('driver-replacement/delete', [DriverAndVanSwapingController::class, 'destroy']);
    Route::delete('driver-replacement/delete/{uuid}', [DriverAndVanSwapingController::class, 'destroyByUuid']);

    // Transactional documents — parent + line items
    Route::get('order/list', [OrderController::class, 'list']);
    Route::get('order/all', [OrderController::class, 'all']);
    Route::post('order/search', [OrderController::class, 'search']);
    Route::get('order/edit/{uuid}', [OrderController::class, 'show']);
    Route::post('order/add', [OrderController::class, 'store']);
    Route::post('order/edit/{uuid}', [OrderController::class, 'update']);
    Route::post('order/delete', [OrderController::class, 'destroy']);
    Route::post('order/bulk-action', [OrderController::class, 'bulkAction']);

    Route::get('delivery/list', [DeliveryController::class, 'list']);
    Route::get('delivery/all', [DeliveryController::class, 'all']);
    Route::post('delivery/search', [DeliveryController::class, 'search']);
    Route::get('delivery/edit/{uuid}', [DeliveryController::class, 'show']);
    Route::post('delivery/add', [DeliveryController::class, 'store']);
    Route::post('delivery/edit/{uuid}', [DeliveryController::class, 'update']);
    Route::post('delivery/delete', [DeliveryController::class, 'destroy']);
    Route::post('delivery/bulk-action', [DeliveryController::class, 'bulkAction']);

    Route::get('invoice/list', [InvoiceController::class, 'list']);
    Route::get('invoice/all', [InvoiceController::class, 'all']);
    Route::post('invoice/search', [InvoiceController::class, 'search']);
    Route::get('invoice/edit/{uuid}', [InvoiceController::class, 'show']);
    Route::post('invoice/add', [InvoiceController::class, 'store']);
    Route::post('invoice/edit/{uuid}', [InvoiceController::class, 'update']);
    Route::post('invoice/delete', [InvoiceController::class, 'destroy']);
    Route::post('invoice/bulk-action', [InvoiceController::class, 'bulkAction']);

    Route::get('debit-note/list', [DebitNoteController::class, 'list']);
    Route::get('debit-note/all', [DebitNoteController::class, 'all']);
    Route::post('debit-note/search', [DebitNoteController::class, 'search']);
    Route::get('debit-note/edit/{uuid}', [DebitNoteController::class, 'show']);
    Route::post('debit-note/add', [DebitNoteController::class, 'store']);
    Route::post('debit-note/edit/{uuid}', [DebitNoteController::class, 'update']);
    Route::post('debit-note/delete', [DebitNoteController::class, 'destroy']);
    Route::post('debit-note/bulk-action', [DebitNoteController::class, 'bulkAction']);

    Route::get('salesman-load/list', [SalesmanLoadController::class, 'list']);
    Route::get('salesman-load/all', [SalesmanLoadController::class, 'all']);
    Route::post('salesman-load/search', [SalesmanLoadController::class, 'search']);
    Route::get('salesman-load/edit/{uuid}', [SalesmanLoadController::class, 'show']);
    Route::post('salesman-load/add', [SalesmanLoadController::class, 'store']);
    Route::post('salesman-load/edit/{uuid}', [SalesmanLoadController::class, 'update']);
    Route::post('salesman-load/delete', [SalesmanLoadController::class, 'destroy']);
    Route::post('salesman-load/bulk-action', [SalesmanLoadController::class, 'bulkAction']);
});
