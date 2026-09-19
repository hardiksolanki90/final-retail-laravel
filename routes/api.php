<?php

use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\BankInformationController;
use App\Http\Controllers\Api\BeatController;
use App\Http\Controllers\Api\ChannelController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\CountryMasterController;
use App\Http\Controllers\Api\CreditNoteController;
use App\Http\Controllers\Api\GoodReceiptNoteController;
use App\Http\Controllers\Api\ConsumerSurveyController;
use App\Http\Controllers\Api\JourneyPlanController;
use App\Http\Controllers\Api\DivisionController;
use App\Http\Controllers\Api\PalletController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\WorkFlowRuleController;
use App\Http\Controllers\Api\InviteUserController;
use App\Http\Controllers\Api\SalesmanUnloadController;
use App\Http\Controllers\Api\SensorySurveyController;
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
use App\Http\Controllers\Api\ItemCategoryController;
use App\Http\Controllers\Api\BrandController;
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
use App\Http\Controllers\Api\SalesmanTypeController;
use App\Http\Controllers\Api\SalesmanRoleController;
use App\Http\Controllers\Api\SalesmanLoadController;
use App\Http\Controllers\Api\SalesOrganisationController;
use App\Http\Controllers\Api\TaxRateController;
use App\Http\Controllers\Api\UserCreditLimitController;
use App\Http\Controllers\Api\VanCategoryController;
use App\Http\Controllers\Api\VanController;
use App\Http\Controllers\Api\VanTypeController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ZoneController;
use Illuminate\Support\Facades\Route;

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);
Route::get('country/public-list', [CountryController::class, 'publicList']);

Route::middleware(['auth:sanctum', \App\Http\Middleware\SetPermissionsTeam::class])->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/user', [AuthController::class, 'user']);

    Route::get('organisation/current', [OrganisationController::class, 'current']);
    Route::get('organisation/details', [OrganisationController::class, 'details']);
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

    Route::get('salesman-type/all', [SalesmanTypeController::class, 'all']);
    Route::get('salesman-type/edit/{uuid}', [SalesmanTypeController::class, 'show']);
    Route::post('salesman-type/add', [SalesmanTypeController::class, 'store']);
    Route::post('salesman-type/edit/{uuid}', [SalesmanTypeController::class, 'update']);
    Route::post('salesman-type/delete', [SalesmanTypeController::class, 'destroy']);

    Route::get('salesman-role/all', [SalesmanRoleController::class, 'all']);
    Route::get('salesman-role/edit/{uuid}', [SalesmanRoleController::class, 'show']);
    Route::post('salesman-role/add', [SalesmanRoleController::class, 'store']);
    Route::post('salesman-role/edit/{uuid}', [SalesmanRoleController::class, 'update']);
    Route::post('salesman-role/delete', [SalesmanRoleController::class, 'destroy']);

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

    Route::get('customer-category/list', [CustomerCategoryController::class, 'list']);
    Route::get('customer-category/all', [CustomerCategoryController::class, 'all']);
    Route::get('customer-category/view/{uuid}', [CustomerCategoryController::class, 'show']);
    Route::post('customer-category/add', [CustomerCategoryController::class, 'store']);
    Route::post('customer-category/edit/{uuid}', [CustomerCategoryController::class, 'update']);
    Route::delete('customer-category/delete/{uuid}', [CustomerCategoryController::class, 'destroyByUuid']);

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

    Route::get('sales-organisation/list', [SalesOrganisationController::class, 'list']);
    Route::get('sales-organisation/all', [SalesOrganisationController::class, 'all']);
    Route::get('sales-organisation/edit/{uuid}', [SalesOrganisationController::class, 'show']);
    Route::get('sales-organisation/view/{uuid}', [SalesOrganisationController::class, 'show']);
    Route::post('sales-organisation/add', [SalesOrganisationController::class, 'store']);
    Route::post('sales-organisation/edit/{uuid}', [SalesOrganisationController::class, 'update']);
    Route::delete('sales-organisation/delete/{uuid}', [SalesOrganisationController::class, 'destroyByUuid']);
    Route::post('sales-organisation/delete', [SalesOrganisationController::class, 'destroy']);

    Route::get('payment-term/all', [PaymentTermController::class, 'all']);
    Route::get('payment-term/edit/{uuid}', [PaymentTermController::class, 'show']);
    Route::post('payment-term/add', [PaymentTermController::class, 'store']);
    Route::post('payment-term/edit/{uuid}', [PaymentTermController::class, 'update']);
    Route::post('payment-term/delete', [PaymentTermController::class, 'destroy']);

    Route::get('route/list', [RouteController::class, 'list']);
    Route::get('route/all', [RouteController::class, 'all']);
    Route::get('route/view/{uuid}', [RouteController::class, 'show']);
    Route::post('route/add', [RouteController::class, 'store']);
    Route::post('route/edit/{uuid}', [RouteController::class, 'update']);
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
    Route::get('tax-rate/types', [TaxRateController::class, 'types']);
    Route::get('tax-rate/edit/{uuid}', [TaxRateController::class, 'show']);
    Route::post('tax-rate/add', [TaxRateController::class, 'store']);
    Route::post('tax-rate/edit/{uuid}', [TaxRateController::class, 'update']);
    Route::post('tax-rate/delete', [TaxRateController::class, 'destroy']);

    Route::get('bank/list', [BankInformationController::class, 'list']);
    Route::get('bank/all', [BankInformationController::class, 'all']);
    Route::get('bank/view/{uuid}', [BankInformationController::class, 'show']);
    Route::post('bank/add', [BankInformationController::class, 'store']);
    Route::post('bank/edit/{uuid}', [BankInformationController::class, 'update']);
    Route::delete('bank/delete/{uuid}', [BankInformationController::class, 'destroyByUuid']);

    Route::get('country/list', [CountryController::class, 'list']);
    Route::get('country/all', [CountryController::class, 'all']);
    Route::get('country-master/all', [CountryMasterController::class, 'all']);
    Route::get('country/view/{uuid}', [CountryController::class, 'show']);
    Route::post('country/add', [CountryController::class, 'store']);
    Route::post('country/edit/{uuid}', [CountryController::class, 'update']);
    Route::delete('country/delete/{uuid}', [CountryController::class, 'destroyByUuid']);

    Route::get('item-category/list', [ItemCategoryController::class, 'list']);
    Route::get('item-category/all', [ItemCategoryController::class, 'all']);
    Route::get('item-category/view/{uuid}', [ItemCategoryController::class, 'show']);
    Route::post('item-category/add', [ItemCategoryController::class, 'store']);
    Route::post('item-category/edit/{uuid}', [ItemCategoryController::class, 'update']);
    Route::delete('item-category/delete/{uuid}', [ItemCategoryController::class, 'destroyByUuid']);

    Route::get('brand/list', [BrandController::class, 'list']);
    Route::get('brand/all', [BrandController::class, 'all']);
    Route::get('brand/view/{uuid}', [BrandController::class, 'show']);
    Route::post('brand/add', [BrandController::class, 'store']);
    Route::post('brand/edit/{uuid}', [BrandController::class, 'update']);
    Route::delete('brand/delete/{uuid}', [BrandController::class, 'destroyByUuid']);

    Route::get('item-group/list', [ItemGroupController::class, 'list']);
    Route::get('item-group/all', [ItemGroupController::class, 'all']);
    Route::get('item-group/view/{uuid}', [ItemGroupController::class, 'show']);
    Route::post('item-group/add', [ItemGroupController::class, 'store']);
    Route::post('item-group/edit/{uuid}', [ItemGroupController::class, 'update']);
    Route::delete('item-group/delete/{uuid}', [ItemGroupController::class, 'destroyByUuid']);

    Route::get('item-uom/list', [ItemUomController::class, 'list']);
    Route::get('item-uom/all', [ItemUomController::class, 'all']);
    Route::get('item-uom/view/{uuid}', [ItemUomController::class, 'show']);
    Route::post('item-uom/add', [ItemUomController::class, 'store']);
    Route::post('item-uom/edit/{uuid}', [ItemUomController::class, 'update']);
    Route::delete('item-uom/delete/{uuid}', [ItemUomController::class, 'destroyByUuid']);

    Route::get('reason-type/list', [ReasonTypeController::class, 'list']);
    Route::get('reason-type/all', [ReasonTypeController::class, 'all']);
    Route::get('reason-type/view/{uuid}', [ReasonTypeController::class, 'show']);
    Route::post('reason-type/add', [ReasonTypeController::class, 'store']);
    Route::post('reason-type/edit/{uuid}', [ReasonTypeController::class, 'update']);
    Route::delete('reason-type/delete/{uuid}', [ReasonTypeController::class, 'destroyByUuid']);

    Route::get('outlet-product-code/list', [OutletProductCodeController::class, 'list']);
    Route::get('outlet-product-code/all', [OutletProductCodeController::class, 'all']);
    Route::get('outlet-product-code/view/{uuid}', [OutletProductCodeController::class, 'show']);
    Route::post('outlet-product-code/add', [OutletProductCodeController::class, 'store']);
    Route::post('outlet-product-code/edit/{uuid}', [OutletProductCodeController::class, 'update']);
    Route::delete('outlet-product-code/delete/{uuid}', [OutletProductCodeController::class, 'destroyByUuid']);

    Route::get('zone/list', [ZoneController::class, 'list']);
    Route::get('zone/all', [ZoneController::class, 'all']);
    Route::get('zone/edit/{uuid}', [ZoneController::class, 'show']);
    Route::post('zone/add', [ZoneController::class, 'store']);
    Route::post('zone/edit/{uuid}', [ZoneController::class, 'update']);
    Route::post('zone/delete', [ZoneController::class, 'destroy']);

    Route::get('currency/list', [CurrencyController::class, 'list']);
    Route::get('currency/all', [CurrencyController::class, 'all']);
    Route::get('currency/view/{uuid}', [CurrencyController::class, 'show']);
    Route::post('currency/add', [CurrencyController::class, 'store']);
    Route::post('currency/edit/{uuid}', [CurrencyController::class, 'update']);
    Route::delete('currency/delete/{uuid}', [CurrencyController::class, 'destroyByUuid']);

    Route::get('currency-master/all', [CurrencyMasterController::class, 'all']);

    Route::get('region/list', [RegionController::class, 'list']);
    Route::get('region/all', [RegionController::class, 'all']);
    Route::get('region/view/{uuid}', [RegionController::class, 'show']);
    Route::post('region/add', [RegionController::class, 'store']);
    Route::post('region/edit/{uuid}', [RegionController::class, 'update']);
    Route::delete('region/delete/{uuid}', [RegionController::class, 'destroyByUuid']);

    Route::get('area/list', [AreaController::class, 'list']);
    Route::get('area/all', [AreaController::class, 'all']);
    Route::get('area/view/{uuid}', [AreaController::class, 'show']);
    Route::post('area/add', [AreaController::class, 'store']);
    Route::post('area/edit/{uuid}', [AreaController::class, 'update']);
    Route::delete('area/delete/{uuid}', [AreaController::class, 'destroyByUuid']);

    Route::get('beat/list', [BeatController::class, 'list']);
    Route::get('beat/all', [BeatController::class, 'all']);
    Route::get('beat/view/{uuid}', [BeatController::class, 'show']);
    Route::post('beat/add', [BeatController::class, 'store']);
    Route::post('beat/edit/{uuid}', [BeatController::class, 'update']);
    Route::delete('beat/delete/{uuid}', [BeatController::class, 'destroyByUuid']);

    Route::get('depot/list', [DepotController::class, 'list']);
    Route::get('depot/all', [DepotController::class, 'all']);
    Route::get('depot/view/{uuid}', [DepotController::class, 'show']);
    Route::post('depot/add', [DepotController::class, 'store']);
    Route::post('depot/edit/{uuid}', [DepotController::class, 'update']);
    Route::delete('depot/delete/{uuid}', [DepotController::class, 'destroyByUuid']);

    Route::get('van-type/all', [VanTypeController::class, 'all']);
    Route::post('van-type/add', [VanTypeController::class, 'store']);
    Route::get('van-category/all', [VanCategoryController::class, 'all']);
    Route::post('van-category/add', [VanCategoryController::class, 'store']);

    Route::get('user-credit-limit/list', [UserCreditLimitController::class, 'list']);
    Route::get('user-credit-limit/all', [UserCreditLimitController::class, 'all']);
    Route::get('user-credit-limit/edit/{uuid}', [UserCreditLimitController::class, 'show']);
    Route::post('user-credit-limit/add', [UserCreditLimitController::class, 'store']);
    Route::post('user-credit-limit/edit/{uuid}', [UserCreditLimitController::class, 'update']);
    Route::post('user-credit-limit/delete', [UserCreditLimitController::class, 'destroy']);

    // Settings masters — paginated CRUD (list/all/show/store/update/destroy/destroyByUuid), Route-style
    Route::get('van/list', [VanController::class, 'list']);
    Route::get('van/all', [VanController::class, 'all']);
    Route::get('van/view/{uuid}', [VanController::class, 'show']);
    Route::post('van/add', [VanController::class, 'store']);
    Route::post('van/edit/{uuid}', [VanController::class, 'update']);
    Route::delete('van/delete/{uuid}', [VanController::class, 'destroyByUuid']);

    Route::get('warehouse/list', [WarehouseController::class, 'list']);
    Route::get('warehouse/all', [WarehouseController::class, 'all']);
    Route::get('warehouse/view/{uuid}', [WarehouseController::class, 'show']);
    Route::post('warehouse/add', [WarehouseController::class, 'store']);
    Route::post('warehouse/edit/{uuid}', [WarehouseController::class, 'update']);
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

    Route::get('credit-note/list', [CreditNoteController::class, 'list']);
    Route::get('credit-note/all', [CreditNoteController::class, 'all']);
    Route::post('credit-note/search', [CreditNoteController::class, 'search']);
    Route::get('credit-note/edit/{uuid}', [CreditNoteController::class, 'show']);
    Route::post('credit-note/add', [CreditNoteController::class, 'store']);
    Route::post('credit-note/edit/{uuid}', [CreditNoteController::class, 'update']);
    Route::post('credit-note/delete', [CreditNoteController::class, 'destroy']);
    Route::post('credit-note/bulk-action', [CreditNoteController::class, 'bulkAction']);

    Route::get('grn/list', [GoodReceiptNoteController::class, 'list']);
    Route::get('grn/all', [GoodReceiptNoteController::class, 'all']);
    Route::post('grn/search', [GoodReceiptNoteController::class, 'search']);
    Route::get('grn/edit/{uuid}', [GoodReceiptNoteController::class, 'show']);
    Route::post('grn/add', [GoodReceiptNoteController::class, 'store']);
    Route::post('grn/edit/{uuid}', [GoodReceiptNoteController::class, 'update']);
    Route::post('grn/delete', [GoodReceiptNoteController::class, 'destroy']);
    Route::post('grn/bulk-action', [GoodReceiptNoteController::class, 'bulkAction']);

    Route::get('journey-plan/list', [JourneyPlanController::class, 'list']);
    Route::get('journey-plan/all', [JourneyPlanController::class, 'all']);
    Route::post('journey-plan/search', [JourneyPlanController::class, 'search']);
    Route::get('journey-plan/edit/{uuid}', [JourneyPlanController::class, 'show']);
    Route::post('journey-plan/add', [JourneyPlanController::class, 'store']);
    Route::post('journey-plan/edit/{uuid}', [JourneyPlanController::class, 'update']);
    Route::post('journey-plan/delete', [JourneyPlanController::class, 'destroy']);
    Route::post('journey-plan/bulk-action', [JourneyPlanController::class, 'bulkAction']);

    Route::get('consumer-survey/list', [ConsumerSurveyController::class, 'list']);
    Route::get('consumer-survey/all', [ConsumerSurveyController::class, 'all']);
    Route::post('consumer-survey/search', [ConsumerSurveyController::class, 'search']);
    Route::get('consumer-survey/edit/{uuid}', [ConsumerSurveyController::class, 'show']);
    Route::post('consumer-survey/add', [ConsumerSurveyController::class, 'store']);
    Route::post('consumer-survey/edit/{uuid}', [ConsumerSurveyController::class, 'update']);
    Route::post('consumer-survey/delete', [ConsumerSurveyController::class, 'destroy']);
    Route::post('consumer-survey/bulk-action', [ConsumerSurveyController::class, 'bulkAction']);

    Route::get('sensory-survey/list', [SensorySurveyController::class, 'list']);
    Route::get('sensory-survey/all', [SensorySurveyController::class, 'all']);
    Route::post('sensory-survey/search', [SensorySurveyController::class, 'search']);
    Route::get('sensory-survey/edit/{uuid}', [SensorySurveyController::class, 'show']);
    Route::post('sensory-survey/add', [SensorySurveyController::class, 'store']);
    Route::post('sensory-survey/edit/{uuid}', [SensorySurveyController::class, 'update']);
    Route::post('sensory-survey/delete', [SensorySurveyController::class, 'destroy']);
    Route::post('sensory-survey/bulk-action', [SensorySurveyController::class, 'bulkAction']);

    Route::get('permission/all', [PermissionController::class, 'all']);

    Route::get('role/list', [RoleController::class, 'list']);
    Route::get('role/all', [RoleController::class, 'all']);
    Route::get('role/edit/{uuid}', [RoleController::class, 'show']);
    Route::post('role/add', [RoleController::class, 'store']);
    Route::post('role/edit/{uuid}', [RoleController::class, 'update']);
    Route::post('role/delete', [RoleController::class, 'destroy']);
    Route::post('role/bulk-action', [RoleController::class, 'bulkAction']);

    Route::get('invite-user/list', [InviteUserController::class, 'list']);
    Route::get('invite-user/edit/{uuid}', [InviteUserController::class, 'show']);
    Route::post('invite-user/add', [InviteUserController::class, 'store']);
    Route::post('invite-user/edit/{uuid}', [InviteUserController::class, 'update']);
    Route::delete('invite-user/delete/{uuid}', [InviteUserController::class, 'destroy']);

    Route::get('work-flow/list', [WorkFlowRuleController::class, 'list']);
    Route::get('work-flow/approver-options', [WorkFlowRuleController::class, 'approverOptions']);
    Route::get('work-flow/edit/{uuid}', [WorkFlowRuleController::class, 'show']);
    Route::post('work-flow/add', [WorkFlowRuleController::class, 'store']);
    Route::post('work-flow/edit/{uuid}', [WorkFlowRuleController::class, 'update']);
    Route::delete('work-flow/delete/{uuid}', [WorkFlowRuleController::class, 'destroy']);

    Route::get('division/list', [DivisionController::class, 'list']);
    Route::get('division/all', [DivisionController::class, 'all']);
    Route::get('division/edit/{uuid}', [DivisionController::class, 'show']);
    Route::post('division/add', [DivisionController::class, 'store']);
    Route::post('division/edit/{uuid}', [DivisionController::class, 'update']);
    Route::post('division/delete', [DivisionController::class, 'destroy']);
    Route::post('division/bulk-action', [DivisionController::class, 'bulkAction']);

    Route::get('pallet/list', [PalletController::class, 'list']);
    Route::get('pallet/edit/{uuid}', [PalletController::class, 'show']);
    Route::post('pallet/add', [PalletController::class, 'store']);
    Route::post('pallet/delete', [PalletController::class, 'destroy']);
    Route::post('pallet/bulk-action', [PalletController::class, 'bulkAction']);

    Route::get('salesman-load/list', [SalesmanLoadController::class, 'list']);
    Route::get('salesman-load/all', [SalesmanLoadController::class, 'all']);
    Route::post('salesman-load/search', [SalesmanLoadController::class, 'search']);
    Route::get('salesman-load/edit/{uuid}', [SalesmanLoadController::class, 'show']);
    Route::post('salesman-load/add', [SalesmanLoadController::class, 'store']);
    Route::post('salesman-load/edit/{uuid}', [SalesmanLoadController::class, 'update']);
    Route::post('salesman-load/delete', [SalesmanLoadController::class, 'destroy']);
    Route::post('salesman-load/bulk-action', [SalesmanLoadController::class, 'bulkAction']);

    Route::get('salesman-unload/list', [SalesmanUnloadController::class, 'list']);
    Route::get('salesman-unload/all', [SalesmanUnloadController::class, 'all']);
    Route::post('salesman-unload/search', [SalesmanUnloadController::class, 'search']);
    Route::get('salesman-unload/edit/{uuid}', [SalesmanUnloadController::class, 'show']);
    Route::post('salesman-unload/add', [SalesmanUnloadController::class, 'store']);
    Route::post('salesman-unload/edit/{uuid}', [SalesmanUnloadController::class, 'update']);
    Route::post('salesman-unload/delete', [SalesmanUnloadController::class, 'destroy']);
    Route::post('salesman-unload/bulk-action', [SalesmanUnloadController::class, 'bulkAction']);
});
