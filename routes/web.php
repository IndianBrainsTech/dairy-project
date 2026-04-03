<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MetricaController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SheetController;
use App\Http\Controllers\JobWorkController;
use App\Http\Controllers\SalesReturnController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\IncentiveController;
use App\Http\Controllers\DieselBillController;
use App\Http\Controllers\TransportController;
use App\Http\Controllers\TallyController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\ExplorerController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\WorkController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\Masters\Pricing\PriceMasterController;
use App\Http\Controllers\Masters\Purchase\SupplierController;
use App\Http\Controllers\Masters\Purchase\LocationController;
use App\Http\Controllers\Masters\Purchase\DepartmentController;
use App\Http\Controllers\Masters\Purchase\Items\PurchaseItemGroupController;
use App\Http\Controllers\Masters\Purchase\Items\PurchaseItemUnitController;
use App\Http\Controllers\Masters\Purchase\Items\PurchaseItemController;
use App\Http\Controllers\Transactions\CreditNoteController;


use App\Http\Controllers\Transport\VehicleCategoryController;
use App\Http\Controllers\Transport\VehicleController;
use App\Http\Controllers\Transport\SupplierTransporterController;
use App\Http\Controllers\Transport\VehicleRouteMappingController;
use App\Http\Controllers\Transport\VehicleInsuranceController;
use App\Http\Controllers\Transport\VehicleServiceController;
use App\Http\Controllers\Transport\TripSheetController;
use App\Http\Controllers\Transport\TripSheetMarketController;
use App\Http\Controllers\Transport\TransportAdjustmentController;
use App\Http\Controllers\Transport\TransportBillController;
use App\Http\Controllers\Transport\SecondaryTransportController;
use App\Http\Controllers\Transport\SecondaryTransportBillController;
use App\Http\Controllers\Transport\SecondaryPaymentAbstractController;

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

Auth::routes();

Route::get('/', function () {
    return redirect('login');
});

Route::get('/home', [AdminController::class, 'index'])->name('home');
Route::get('/logout', [AdminController::class, 'logout'])->name('logout');


/* ----------------------------- Masters Start ----------------------------- */

// Customers
Route::prefix('customers')->name('customers.')->group(function () {
    Route::get('/', [CustomerController::class, 'indexCustomers'])->middleware('permission:index_customer')->name('index');
    Route::get('/create', [CustomerController::class, 'createCustomer'])->middleware('permission:create_customer')->name('create');
    Route::post('/', [CustomerController::class, 'storeCustomer'])->middleware('permission:create_customer')->name('store');
    Route::get('/view', [CustomerController::class, 'showCustomer'])->middleware('permission:show_customer')->name('show');
    Route::get('/edit', [CustomerController::class, 'editCustomer'])->middleware('permission:update_customer')->name('edit');
    Route::post('/update/{id}', [CustomerController::class, 'updateCustomer'])->middleware('permission:update_customer')->name('update');
    Route::delete('/delete/{id}', [CustomerController::class, 'destroyCustomer'])->middleware('permission:destroy_customer')->name('destroy');
    Route::get('/status/{id}', [CustomerController::class, 'statusCustomer'])->middleware('permission:toggle_customer')->name('status');
    Route::get('/convert', [CustomerController::class, 'convertCustomer'])->name('convert');

    // Route::get('/area/{id}', [CustomerController::class, 'getCustomersByArea'])->name('get.area');
    Route::get('/route/{id}', [CustomerController::class, 'getCustomersByRoute'])->name('get.route');

    Route::get('/outstanding', [CustomerController::class, 'customerOutstanding'])->middleware('permission:index_outstanding')->name('outstanding');
    Route::post('/outstanding', [CustomerController::class, 'updateCustomerOutstanding'])->middleware('permission:update_outstanding')->name('outstanding');
    Route::get('/credit-limit', [CustomerController::class, 'customerCreditLimit'])->middleware('permission:index_credit_limit')->name('credit.limit');
    Route::post('/credit-limit', [CustomerController::class, 'updateCustomerCreditLimit'])->middleware('permission:update_credit_limit')->name('credit.limit');
    Route::get('/turnover', [CustomerController::class, 'customerTurnover'])->middleware('permission:index_turnover')->name('turnover');
    Route::post('/turnover', [CustomerController::class, 'updateCustomerTurnover'])->middleware('permission:update_turnover')->name('turnover');
    
    // Route::get('/data/tcs', [CustomerController::class, 'getTcsData'])->name('data.tcs');
    Route::get('/data/address/{cust_id}', [CustomerController::class, 'getAddressData'])->name('data.address');
    Route::get('/data/address-tcs/{cust_id}', [CustomerController::class, 'getAddressAndTcsData'])->name('data.addr-tcs');
    Route::get('/data/billing/{cust_id}', [CustomerController::class, 'getCustomerBillingData'])->name('data.billing');
});
// End Customers

// Employees
Route::get('/employees', [ProfileController::class, 'indexEmployees'])->name('employees.index');
Route::get('/employee/create', [ProfileController::class, 'createEmployee'])->name('employees.create');
Route::post('/employee', [ProfileController::class, 'storeEmployee'])->name('employees.store');
Route::get('/employee/view', [ProfileController::class, 'showEmployee'])->name('employees.show');
Route::get('/employee/edit', [ProfileController::class, 'editEmployee'])->name('employees.edit');
Route::post('/employee/{id}', [ProfileController::class, 'updateEmployee'])->name('employees.update');
Route::get('/employee/status/{id}', [ProfileController::class, 'statusEmployee'])->name('employees.status');
Route::get('/employee/manager/{id}', [ProfileController::class, 'managerEmployee'])->name('employees.manager');

// Competitors
Route::get('/competitors', [ProfileController::class, 'indexCompetitors'])->name('competitors.index');
Route::get('/competitor/{id}', [ProfileController::class, 'editCompetitor'])->name('competitors.edit');
Route::post('/competitor/{id}', [ProfileController::class, 'storeCompetitor'])->name('competitors.store');
Route::delete('/competitor/{id}', [ProfileController::class, 'destroyCompetitor'])->name('competitors.destroy');

// Roles
Route::get('/roles', [ProfileController::class, 'indexRoles'])->name('roles.index');

// Photos
Route::post('/photos', [PhotoController::class, 'updatePhoto'])->name('photos.update');

// Products
Route::get('/products', [ProductController::class, 'indexProducts'])->name('products.index');
Route::get('/product/create', [ProductController::class, 'createProduct'])->name('products.create');
Route::post('/product', [ProductController::class, 'storeProduct'])->name('products.store');
Route::get('/product/view', [ProductController::class, 'showProduct'])->name('products.show');
Route::get('/product/edit', [ProductController::class, 'editProduct'])->name('products.edit');
Route::post('/product/{id}', [ProductController::class, 'updateProduct'])->name('products.update');
Route::get('/product/status/{id}', [ProductController::class, 'statusProduct'])->name('products.status');
Route::get('/product/unique', [ProductController::class, 'uniqueProduct'])->name('products.unique');
Route::post('/product-reorder', [ProductController::class, 'reorderProduct'])->name('products.reorder');

// Product Groups
Route::get('/product_groups', [ProductController::class, 'indexProductGroups'])->name('prgroups.index');
Route::get('/product_group/{id}', [ProductController::class, 'editProductGroup'])->name('prgroups.edit');
Route::post('/product_group/{id}', [ProductController::class, 'storeProductGroup'])->name('prgroups.store');
Route::delete('/product_group/{id}', [ProductController::class, 'destroyProductGroup'])->name('prgroups.destroy');

// Units
Route::get('/units', [ProductController::class, 'indexUnits'])->name('units.index');
Route::get('/unit/{id}', [ProductController::class, 'editUnit'])->name('units.edit');
Route::post('/unit/{id}', [ProductController::class, 'storeUnit'])->name('units.store');
Route::delete('/unit/{id}', [ProductController::class, 'destroyUnit'])->name('units.destroy');

Route::prefix('purchase')->name('purchase.')->group(function () {
    Route::prefix('items')->name('items.')->group(function () {
        Route::prefix('groups')->name('groups.')->group(function () {
            Route::get('/',            [PurchaseItemGroupController::class, 'index'])   -> name('index');
            Route::get('/list',        [PurchaseItemGroupController::class, 'list'])    -> name('list');
            Route::post('/',           [PurchaseItemGroupController::class, 'store'])   -> name('store');
            Route::put('/{master}',    [PurchaseItemGroupController::class, 'update'])  -> name('update');
            Route::delete('/{master}', [PurchaseItemGroupController::class, 'destroy']) -> name('destroy');
            Route::patch('/{master}',  [PurchaseItemGroupController::class, 'toggle'])  -> name('toggle');
        });

        Route::prefix('units')->name('units.')->group(function () {
            Route::get('/',            [PurchaseItemUnitController::class, 'index'])   -> name('index');
            Route::get('/list',        [PurchaseItemUnitController::class, 'list'])    -> name('list');
            Route::post('/',           [PurchaseItemUnitController::class, 'store'])   -> name('store');
            Route::put('/{master}',    [PurchaseItemUnitController::class, 'update'])  -> name('update');
            Route::delete('/{master}', [PurchaseItemUnitController::class, 'destroy']) -> name('destroy');
            Route::patch('/{master}',  [PurchaseItemUnitController::class, 'toggle'])  -> name('toggle');
        });

        Route::prefix('items')->name('items.')->group(function () {        
            Route::get('/',            [PurchaseItemController::class, 'index'])   -> name('index');
            Route::get('/list',        [PurchaseItemController::class, 'list'])    -> name('list');
            Route::get('/create',      [PurchaseItemController::class, 'create'])  -> name('create');
            Route::post('/',           [PurchaseItemController::class, 'store'])   -> name('store');
            Route::get('/{master}',    [PurchaseItemController::class, 'show'])    -> name('show');
            Route::get('/{master}/edit', [PurchaseItemController::class, 'edit'])  -> name('edit');
            Route::put('/{master}',    [PurchaseItemController::class, 'update'])  -> name('update');
            Route::delete('/{master}', [PurchaseItemController::class, 'destroy']) -> name('destroy');
            Route::patch('/{master}',  [PurchaseItemController::class, 'toggle'])  -> name('toggle');
        });
    });
});

// Suppliers
Route::resource('suppliers', SupplierController::class);
Route::prefix('suppliers')->name('suppliers.')->group(function () {        
    Route::get('/list',        [SupplierController::class, 'list'])    -> name('list');
    Route::patch('/{master}',  [SupplierController::class, 'toggle'])  -> name('toggle');
});

// Locations
Route::resource('locations', LocationController::class)->only(['index','store','update','destroy']);
Route::patch('locations/{location}/toggle', [LocationController::class, 'toggle'])->name('locations.toggle');

// Departments
Route::resource('departments', DepartmentController::class)->only(['index','store','update','destroy']);
Route::patch('departments/{department}/toggle', [DepartmentController::class, 'toggle'])->name('departments.toggle');

// Places
Route::prefix('states')->name('states.')->group(function () {
    Route::get('/list', [PlaceController::class, 'listStates'])->name('list');
    Route::get('/', [PlaceController::class, 'indexStates'])->name('index');
    Route::get('/{id}', [PlaceController::class, 'editState'])->name('edit');
    Route::post('/{id}', [PlaceController::class, 'storeState'])->name('store');
    Route::delete('/{id}', [PlaceController::class, 'destroyState'])->name('destroy');
    Route::get('/get/{district}', [PlaceController::class, 'getState'])->name('get');
});

Route::prefix('districts')->name('districts.')->group(function () {
    Route::get('/list', [PlaceController::class, 'listDistricts'])->name('list');
    Route::get('/', [PlaceController::class, 'indexDistricts'])->name('index');
    Route::get('/{id}', [PlaceController::class, 'editDistrict'])->name('edit');
    Route::post('/{id}', [PlaceController::class, 'storeDistrict'])->name('store');
    Route::delete('/{id}', [PlaceController::class, 'destroyDistrict'])->name('destroy');
});

Route::prefix('routes')->name('routes.')->group(function () {
    Route::get('/list', [PlaceController::class, 'listRoutes'])->name('list');
    Route::get('/', [PlaceController::class, 'indexRoutes'])->name('index');
    Route::get('/{id}', [PlaceController::class, 'editRoute'])->name('edit');
    Route::post('/{id}', [PlaceController::class, 'storeRoute'])->name('store');
    Route::delete('/{id}', [PlaceController::class, 'destroyRoute'])->name('destroy');
});

Route::prefix('areas')->name('areas.')->group(function () {
    Route::get('/list/{id}', [PlaceController::class, 'listAreas'])->name('list');
    Route::get('/info/{id}', [PlaceController::class, 'getAreaInfo'])->name('info');
    Route::get('/', [PlaceController::class, 'indexAreas'])->name('index');
    Route::get('/{id}', [PlaceController::class, 'editArea'])->name('edit');
    Route::post('/{id}', [PlaceController::class, 'storeArea'])->name('store');
    Route::delete('/{id}', [PlaceController::class, 'destroyArea'])->name('destroy');
});

Route::prefix('address')->name('address.')->group(function () {
    Route::get('/{id}', [PlaceController::class, 'editAddress'])->name('edit');
    Route::post('/', [PlaceController::class, 'storeAddress'])->name('store');
});
// End Places

// Transport => Vehicles
Route::get('/vehicles', [TransportController::class, 'indexVehicles'])->name('vehicles.index');
Route::get('/vehicle/{id}', [TransportController::class, 'editVehicle'])->name('vehicles.edit');
Route::post('/vehicle/{id}', [TransportController::class, 'storeVehicle'])->name('vehicles.store');
Route::get('/vehicle/delete/{id}', [TransportController::class, 'destroyVehicle'])->name('vehicles.destroy');

Route::prefix('bunks')->name('bunks.')->group(function () {        
    Route::get('/',            [TransportController::class, 'indexBunk'])   -> name('index');
    Route::get('/list',        [TransportController::class, 'listBunk'])    -> name('list');
    Route::get('/create',      [TransportController::class, 'createBunk'])  -> name('create');
    Route::post('/',           [TransportController::class, 'storeBunk'])   -> name('store');
    Route::get('/{bunk}',      [TransportController::class, 'showBunk'])    -> name('show');
    Route::get('/{bunk}/edit', [TransportController::class, 'editBunk'])    -> name('edit');
    Route::put('/{bunk}',      [TransportController::class, 'updateBunk'])  -> name('update');
    Route::delete('/{bunk}',   [TransportController::class, 'destroyBunk']) -> name('destroy');
    Route::patch('/{bunk}/status',   [TransportController::class, 'updateBunkStatus'])   ->name('status');
    Route::get('/turnover/index',    [TransportController::class, 'indexBunkTurnover'])  ->name('turnover.index');
    Route::patch('/turnover/update', [TransportController::class, 'updateBunkTurnover']) ->name('turnover.update');
});

// Taxation => GST Master
Route::get('/gst_master', [MasterController::class, 'indexGstMasters'])->name('gstmaster.index');
Route::get('/gst_master/{id}', [MasterController::class, 'editGstMaster'])->name('gstmaster.edit');
Route::post('/gst_master/{id}', [MasterController::class, 'storeGstMaster'])->name('gstmaster.store');
Route::delete('/gst_master/{id}', [MasterController::class, 'destroyGstMaster'])->name('gstmaster.destroy');
Route::get('/gst_master_list', [MasterController::class, 'listGstMasters'])->name('gstmaster.list');
Route::get('/gst_info/{hsn}', [MasterController::class, 'getGstInfo'])->name('gstmaster.info');

// Taxation => TCS Master
Route::get('/tcs_master', [MasterController::class, 'indexTcsMasters'])->name('tcsmaster.index');
Route::post('/tcs_master', [MasterController::class, 'storeTcsMaster'])->name('tcsmaster.store');

// Taxation => TDS Master
Route::get('/tds_master', [MasterController::class, 'indexTdsMasters'])->name('tdsmaster.index');
Route::post('/tds_master', [MasterController::class, 'storeTdsMaster'])->name('tdsmaster.store');

// Deals & Pricing => Price Master
Route::prefix('price-masters')->name('price-masters.')->group(function () {
    Route::get('/',              [PriceMasterController::class, 'indexPriceMaster'])   -> middleware('permission:index_price_master')  -> name('index');    
    Route::get('/create',        [PriceMasterController::class, 'createPriceMaster'])  -> middleware('permission:create_price_master') -> name('create');
    Route::post('/',             [PriceMasterController::class, 'storePriceMaster'])   -> middleware('permission:create_price_master') -> name('store');
    Route::get('/{master}',      [PriceMasterController::class, 'showPriceMaster'])    -> middleware('permission:show_price_master')   -> name('show');
    Route::get('/{master}/edit', [PriceMasterController::class, 'editPriceMaster'])    -> middleware('permission:update_price_master') -> name('edit');
    Route::put('/{master}',      [PriceMasterController::class, 'updatePriceMaster'])  -> middleware('permission:update_price_master') -> name('update');    
    Route::patch('/{master}/status', [PriceMasterController::class, 'togglePriceMasterStatus']) -> middleware('permission:toggle_price_master') -> name('status.toggle');
    Route::get('/{master}/clone', [PriceMasterController::class, 'createPriceMasterClone'])     -> middleware('permission:update_price_master') -> name('clone.create');
    Route::put('/{master}/clone', [PriceMasterController::class, 'updatePriceMasterClone'])     -> middleware('permission:update_price_master') -> name('clone.update');
    Route::get('/adjust/create', [PriceMasterController::class, 'createPriceMasterAdjustment']) -> middleware('permission:adjust_price_master') -> name('adjust.create');
    Route::get('/adjust/fetch',  [PriceMasterController::class, 'fetchMastersForAdjustment'])   -> middleware('permission:adjust_price_master') -> name('adjust.fetch');
    Route::post('/adjust/store', [PriceMasterController::class, 'storePriceMasterAdjustment'])  -> middleware('permission:adjust_price_master') -> name('adjust.store');
});

// Route::get('/price_master', [MasterController::class, 'indexPriceMasters'])->name('price-master.index');
// Route::get('/price_master/create', [MasterController::class, 'createPriceMaster'])->name('price-master.create');
// Route::post('/price_master', [MasterController::class, 'storePriceMaster'])->name('price-master.store');
// Route::get('/price_master/view', [MasterController::class, 'showPriceMaster'])->name('price-master.show');
// Route::get('/price_master/edit', [MasterController::class, 'editPriceMaster'])->name('price-master.edit');
// Route::post('/price_master/{id}', [MasterController::class, 'updatePriceMaster'])->name('price-master.update');
// Route::get('/price_master/status/{id}', [MasterController::class, 'statusPriceMaster'])->name('price-master.status');

// Deals & Pricing => Discount Master
Route::get('/discount_master', [MasterController::class, 'indexDiscountMasters'])->name('discount-master.index');
Route::get('/discount_master/create', [MasterController::class, 'createDiscountMaster'])->name('discount-master.create');
Route::post('/discount_master', [MasterController::class, 'storeDiscountMaster'])->name('discount-master.store');
Route::get('/discount_master/view', [MasterController::class, 'showDiscountMaster'])->name('discount-master.show');
Route::get('/discount_master/edit', [MasterController::class, 'editDiscountMaster'])->name('discount-master.edit');
Route::post('/discount_master/{id}', [MasterController::class, 'updateDiscountMaster'])->name('discount-master.update');
Route::get('/discount_master/status/{id}', [MasterController::class, 'statusDiscountMaster'])->name('discount-master.status');

// Deals & Pricing => Incentive Master
Route::get('/incentive_master', [MasterController::class, 'indexIncentiveMasters'])->name('incentive-master.index');
Route::get('/incentive_master/create', [MasterController::class, 'createIncentiveMaster'])->name('incentive-master.create');
Route::post('/incentive_master', [MasterController::class, 'storeIncentiveMaster'])->name('incentive-master.store');
Route::get('/incentive_master/view', [MasterController::class, 'showIncentiveMaster'])->name('incentive-master.show');
Route::get('/incentive_master/edit', [MasterController::class, 'editIncentiveMaster'])->name('incentive-master.edit');
Route::post('/incentive_master/{id}', [MasterController::class, 'updateIncentiveMaster'])->name('incentive-master.update');
Route::get('/incentive_master/status/{id}', [MasterController::class, 'statusIncentiveMaster'])->name('incentive-master.status');

// Permissions
Route::prefix('permissions')->name('permissions.')->group(function () {
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/',            [PermissionController::class, 'indexRoles'])  -> name('index');
        Route::post('/',           [PermissionController::class, 'storeRole'])   -> name('store');
        Route::get('/{role}/edit', [PermissionController::class, 'editRole'])    -> name('edit');
        Route::put('/{role}',      [PermissionController::class, 'updateRole'])  -> name('update');
        Route::delete('/{role}',   [PermissionController::class, 'destroyRole']) -> name('destroy');
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/',            [PermissionController::class, 'indexUsers'])  -> name('index');
        Route::get('/create',      [PermissionController::class, 'createUser'])  -> name('create');
        Route::post('/',           [PermissionController::class, 'storeUser'])   -> name('store');
        Route::get('/{user}',      [PermissionController::class, 'showUser'])    -> name('show');
        Route::get('/{user}/edit', [PermissionController::class, 'editUser'])    -> name('edit');
        Route::put('/{user}',      [PermissionController::class, 'updateUser'])  -> name('update');
        Route::delete('/{user}',   [PermissionController::class, 'destroyUser']) -> name('destroy');
        
        Route::patch('/{user}/status', [PermissionController::class, 'updateUserStatus'])->name('status');
    });

    Route::prefix('role-permissions')->name('role-permissions.')->group(function () {
        Route::get('/',       [PermissionController::class, 'indexRolePermissions'])  -> name('index');
        Route::get('/{role}', [PermissionController::class, 'showRolePermissions'])   -> name('show');
        Route::put('/{role}', [PermissionController::class, 'updateRolePermissions']) -> name('update');
    });

    Route::prefix('user-permissions')->name('user-permissions.')->group(function () {
        Route::get('/',       [PermissionController::class, 'indexUserPermissions'])  -> name('index');
        Route::get('/{user}', [PermissionController::class, 'showUserPermissions'])   -> name('show');
        Route::put('/{user}', [PermissionController::class, 'updateUserPermissions']) -> name('update');
    });
});

// Banks
Route::prefix('banks')->name('banks.')->group(function () {
    Route::get('/',            [BankController::class, 'indexBank'])   -> name('index');
    Route::post('/',           [BankController::class, 'storeBank'])   -> name('store');
    Route::get('/{bank}/edit', [BankController::class, 'editBank'])    -> name('edit');
    Route::put('/{bank}',      [BankController::class, 'updateBank'])  -> name('update');
    Route::delete('/{bank}',   [BankController::class, 'destroyBank']) -> name('destroy');
    Route::get('/fetch',       [BankController::class, 'fetchBank'])   -> name('fetch');

    Route::prefix('branches')->name('branches.')->group(function () {
        Route::get('/',              [BankController::class, 'indexBranch'])   -> name('index');
        Route::post('/',             [BankController::class, 'storeBranch'])   -> name('store');
        Route::get('/{branch}/edit', [BankController::class, 'editBranch'])    -> name('edit');
        Route::put('/{branch}',      [BankController::class, 'updateBranch'])  -> name('update');
        Route::delete('/{branch}',   [BankController::class, 'destroyBranch']) -> name('destroy');
        Route::get('/{bank}/fetch',  [BankController::class, 'fetchBranch'])   -> name('fetch');
    });
});

// Bank Account
Route::get('/bank_account', [MasterController::class, 'indexBankAccounts'])->name('bank-account.index');
Route::get('/bank_account/{id}', [MasterController::class, 'editBankAccount'])->name('bank-account.edit');
Route::post('/bank_account/{id}', [MasterController::class, 'storeBankAccount'])->name('bank-account.store');

// Mobile Security
Route::get('/mobile_security', [AdminController::class, 'mobileSecurity'])->name('admin.mobile_security');
Route::delete('/mobile_data/{id}', [AdminController::class, 'destroyMobileData'])->name('admin.mobile.destroy');

// Settings
Route::get('/settings', [MasterController::class, 'settings'])->name('master.settings');
Route::post('/settings/update', [MasterController::class, 'updateSettings'])->name('master.settings.update');
Route::get('/settings/date', [MasterController::class, 'createDateSetting'])->middleware('permission:show_setting_receipt_date')->name('settings.date.create');
Route::put('/settings/date', [MasterController::class, 'updateDateSetting'])->middleware('permission:update_setting_receipt_date')->name('settings.date.update');

/* ----------------------------- Masters End ----------------------------- */

/* ----------------------------- Transactions Start ----------------------------- */

// Enquiries
Route::get('/enquiries', [TransactionController::class, 'indexEnquiries'])->name('enquiries.index');
Route::post('/enquiries', [TransactionController::class, 'indexEnquiries'])->name('enquiries.index');
Route::get('/enquiry/view', [TransactionController::class, 'showEnquiry'])->name('enquiries.show');
Route::get('/followups', [TransactionController::class, 'indexFollowups'])->name('followups.index');
Route::post('/followups', [TransactionController::class, 'indexFollowups'])->name('followups.index');
Route::get('/followup/view', [TransactionController::class, 'showFollowup'])->name('followups.show');
Route::get('/conversions', [TransactionController::class, 'indexConversions'])->name('conversions.index');
Route::get('/attendances', [TransactionController::class, 'indexAttendances'])->name('attendances.index');
Route::post('/attendances', [TransactionController::class, 'indexAttendances'])->name('attendances.index');
Route::get('/dayroute', [TransactionController::class, 'showDayRoute'])->name('dayroute.show');

// Production
Route::get('/stock/entry', [WorkController::class, 'stockEntry'])->name('stock.entry');
Route::post('/stock/store', [WorkController::class, 'stockEntryStore'])->name('stock.store');
Route::post('/stock/update/{id}', [WorkController::class, 'stockUpdate'])->name('stock.update');
Route::get('/entry/edit', [WorkController::class, 'entryEdit'])->name('entry.edit');
Route::get('/stock/listview', [WorkController::class, 'stockListview'])->name('stock.listview');
Route::post('/stock/listview', [WorkController::class, 'stockListview'])->name('stock.listview');
Route::get('/stock/show', [WorkController::class, 'stockShow'])->name('stock.show');
Route::post('/stock/show', [WorkController::class, 'stockShow'])->name('stock.show');
Route::get('/current/stock', [WorkController::class, 'CurrentStockShow'])->name('current.stock');
Route::get('/closing/stock', [WorkController::class, 'ClosingStockShow'])->name('closing.stock');

// Stocks
Route::prefix('stocks')->name('stocks.')->group(function () {
    Route::get('/',               [StockController::class, 'indexStock'])   -> middleware('permission:index_stock')  -> name('index');    
    Route::get('/create',         [StockController::class, 'createStock'])  -> middleware('permission:create_stock') -> name('create');
    Route::post('/',              [StockController::class, 'storeStock'])   -> middleware('permission:create_stock') -> name('store');
    Route::post('/show',          [StockController::class, 'showStock'])    -> middleware('permission:show_stock')   -> name('show');
    Route::get('/{stock}/edit',   [StockController::class, 'editStock'])    -> middleware('permission:update_stock') -> name('edit');
    Route::put('/{stock}',        [StockController::class, 'updateStock'])  -> middleware('permission:update_stock') -> name('update');
    Route::put('/{stock}/cancel', [StockController::class, 'cancelStock'])  -> middleware('permission:cancel_stock') -> name('cancel');
    Route::get('/fetch',          [StockController::class, 'fetchStock'])   -> name('fetch');
    Route::get('/approval',       [StockController::class, 'indexStockApproval'])  -> middleware('permission:approve_stock') -> name('approval.index');
    Route::put('/approval/update',[StockController::class, 'updateStockApproval']) -> middleware('permission:approve_stock') -> name('approval.update');
    Route::get('/current',        [StockController::class, 'currentStock'])        -> middleware('permission:show_current_stock')  -> name('current');
    Route::get('/current/json',   [StockController::class, 'currentStockJson'])    -> middleware('permission:show_current_stock')  -> name('current.json');
    Route::get('/register',       [StockController::class, 'stockRegister'])       -> middleware('permission:show_stock_register') -> name('register');
    Route::get('/register/json',  [StockController::class, 'stockRegisterJson'])   -> middleware('permission:show_stock_register') -> name('register.json');
});

// Orders
Route::prefix('orders')->name('orders.')->group(function () {
    Route::get('/create', [OrderController::class, 'createOrder'])->name('create');
    Route::post('/', [OrderController::class, 'storeOrder'])->name('store');
    Route::get('/list', [OrderController::class, 'indexOrders'])->name('index');
    Route::post('/list', [OrderController::class, 'indexOrders'])->name('index');
    Route::post('/view', [OrderController::class, 'showOrder'])->name('show');
    Route::post('/edit', [OrderController::class, 'editOrder'])->name('edit');
    Route::post('/update', [OrderController::class, 'updateOrder'])->name('update');
    Route::get('/cancel', [OrderController::class, 'cancelOrder'])->name('cancel');
    Route::get('/get/{order_num}', [OrderController::class, 'getOrder'])->name('get');
    Route::get('/last/{cust_id}', [OrderController::class, 'lastOrder'])->name('last');
    // Route::get('/export', [OrderController::class, 'exportOrder'])->name('export');
    // Route::get('/customer/{cust_id}', [OrderController::class, 'getCustomerData'])->name('customer.data');
});

// Invoices
Route::prefix('invoices')->name('invoices.')->group(function () {
    Route::get('/', [InvoiceController::class, 'indexInvoices'])->name('index');
    Route::post('/', [InvoiceController::class, 'indexInvoices'])->name('index');
    Route::get('/create', [InvoiceController::class, 'createInvoices'])->name('create');
    Route::post('/view', [InvoiceController::class, 'showInvoice'])->name('show');
    Route::post('/print', [InvoiceController::class, 'printInvoices'])->name('print');
    Route::get('/orders/get', [InvoiceController::class, 'getOrdersForInvoices'])->name('orders.get');
    Route::post('/build', [InvoiceController::class, 'buildInvoices'])->name('build');

    Route::prefix('cancel')->name('cancel.')->group(function () {
        Route::get('/load', [InvoiceController::class, 'loadInvoicesForCancel'])->name('load');
        Route::post('/show', [InvoiceController::class, 'showInvoiceForCancel'])->name('show');
        Route::post('/', [InvoiceController::class, 'cancelInvoice']);
    });
});

// Sheets
Route::prefix('sheets')->name('sheets.')->group(function () {
    Route::get('/loading-sheet', [SheetController::class, 'showLoadingSheet'])->name('loading-sheet');
    Route::get('/trip-sheet', [SheetController::class, 'showTripSheet'])->name('trip-sheet');
});

// Bulk Milk
Route::prefix('bulk-milk')->name('bulk-milk.')->group(function () {
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/create', [OrderController::class, 'createBulkMilkOrder'])->name('create');
        Route::post('/', [OrderController::class, 'storeBulkMilkOrder'])->name('store');
        Route::get('/list', [OrderController::class, 'indexBulkMilkOrders'])->name('index');
        Route::post('/list', [OrderController::class, 'indexBulkMilkOrders'])->name('index');
        Route::post('/view', [OrderController::class, 'showBulkMilkOrder'])->name('show');
        Route::post('/edit', [OrderController::class, 'editBulkMilkOrder'])->name('edit');
        Route::post('/update', [OrderController::class, 'updateBulkMilkOrder'])->name('update');
        Route::get('/cancel', [OrderController::class, 'cancelBulkMilkOrder'])->name('cancel');
    });
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'indexBulkMilkInvoices'])->name('index');
        Route::post('/', [InvoiceController::class, 'indexBulkMilkInvoices'])->name('index');
        Route::get('/create', [InvoiceController::class, 'createBulkMilkInvoices'])->name('create');
        Route::post('/build', [InvoiceController::class, 'buildBulkMilkInvoice'])->name('build');
        Route::post('/view', [InvoiceController::class, 'showBulkMilkInvoice'])->name('show');
        Route::post('/print', [InvoiceController::class, 'printBulkMilkInvoice'])->name('print');
    });
});

// Job Work
Route::prefix('job-work')->name('job-work.')->group(function () {
    Route::get('/create', [JobWorkController::class, 'createJobWork'])->name('create');
    Route::post('/', [JobWorkController::class, 'storeJobWork'])->name('store');
    Route::get('/list', [JobWorkController::class, 'indexJobWork'])->name('index');
    Route::post('/list', [JobWorkController::class, 'indexJobWork'])->name('index');
    Route::post('/view', [JobWorkController::class, 'showJobWork'])->name('show');
    Route::post('/edit', [JobWorkController::class, 'editJobWork'])->name('edit');
    Route::post('/update', [JobWorkController::class, 'updateJobWork'])->name('update');
    Route::get('/cancel', [JobWorkController::class, 'cancelJobWork'])->name('cancel');
});

// Delivery Challan
Route::prefix('delivery-challan')->name('delivery-challan.')->group(function () {
    Route::get('/', [JobWorkController::class, 'indexDeliveryChallan'])->name('index');
    Route::post('/', [JobWorkController::class, 'indexDeliveryChallan'])->name('index');
    Route::get('/create', [JobWorkController::class, 'createDeliveryChallan'])->name('create');
    Route::post('/build', [JobWorkController::class, 'buildDeliveryChallan'])->name('build');
    Route::post('/view', [JobWorkController::class, 'showDeliveryChallan'])->name('show');
    Route::post('/print', [JobWorkController::class, 'printDeliveryChallan'])->name('print');
});

// Receipts
Route::prefix('receipts')->name('receipts.')->group(function () {
    Route::get('/create', [ReceiptController::class, 'createReceipt'])->name('create');
    Route::get('/', [ReceiptController::class, 'indexReceipts'])->name('index');
    Route::post('/', [ReceiptController::class, 'storeReceipt'])->name('store');
    Route::post('/view', [ReceiptController::class, 'showReceipt'])->name('show');
    Route::post('/edit', [ReceiptController::class, 'editReceipt'])->name('edit');
    Route::post('/update', [ReceiptController::class, 'updateReceipt'])->name('update');
    Route::get('/receivables/{customerId}', [ReceiptController::class, 'getReceivables'])->name('receivables');

    Route::prefix('batch-denomination')->name('batch-denomination.')->group(function () {
        Route::get('/create', [ReceiptController::class, 'createBatchDenomination'])->name('create');
        Route::get('/{routeId}', [ReceiptController::class, 'getBatchDenomination'])->name('get');
        Route::post('/', [ReceiptController::class, 'storeBatchDenomination'])->name('store');
    });

    Route::prefix('make')->name('make.')->group(function () {
        Route::get('/index', [ReceiptController::class, 'indexMakeReceipts'])->name('index');
        Route::post('/index', [ReceiptController::class, 'indexMakeReceipts'])->name('index');
        Route::get('/view', [ReceiptController::class, 'showMakeReceipt'])->name('show');
        Route::get('/generate/{routeId}', [ReceiptController::class, 'generateReceipts'])->name('generate');
    });
});

// Sales Return
Route::prefix('sales_return')->name('sales-return.')->group(function () {
    Route::post('/view', [SalesReturnController::class, 'showSalesReturn'])->name('show');
    Route::get('/get', [SalesReturnController::class, 'getSalesReturn'])->name('get');
    Route::get('/', [SalesReturnController::class, 'indexSalesReturns'])->name('index');
    Route::get('/create', [SalesReturnController::class, 'createSalesReturn'])->name('create');
    Route::post('/', [SalesReturnController::class, 'storeSalesReturn'])->name('store');
    Route::get('/invoices/{cust_id}', [SalesReturnController::class, 'getInvoices'])->name('invoices');
    Route::get('/invoices/items/{inv_num}', [SalesReturnController::class, 'getInvoiceItems'])->name('invoices.items');
});

// Credit Note
Route::resource('credit-notes', CreditNoteController::class)
    ->only(['index','create','store','edit','update','destroy'])
    ->whereNumber('credit_note');

Route::prefix('credit-notes')->name('credit-notes.')->group(function () {
    Route::post('/navigate', [CreditNoteController::class, 'navigate']) -> name('navigate');
    Route::patch('/cancel',  [CreditNoteController::class, 'cancel'])   -> name('cancel');
    Route::get('/approve',   [CreditNoteController::class, 'createApproval']) -> name('approve.create');
    Route::patch('/approve', [CreditNoteController::class, 'updateApproval']) -> name('approve.update');    
});


// Denomination
Route::get('/receipt/denomination', [WorkController::class, 'receiptDenomination'])->name('receipt.denomination');
Route::post('/receipt/denomination', [WorkController::class, 'receiptDenomination'])->name('receipt.denomination');
Route::post('/receipt/view', [WorkController::class, 'receiptDenominationView'])->name('receipt.denomination.view');
Route::get('/day/route/denomination', [WorkController::class, 'dayRouteDenomination'])->name('day.route.denomination');
Route::post('/day/route/denomination', [WorkController::class, 'dayRouteDenomination'])->name('day.route.denomination');
Route::post('/route/denomination/view', [WorkController::class, 'routeDenominationView'])->name('route.denomination.view');
Route::post('/day/denomination/view', [WorkController::class, 'dayDenominationView'])->name('day.denomination.view');

// Incentive
Route::prefix('incentives')->name('incentives.')->group(function () {
    Route::get('/', [IncentiveController::class, 'indexIncentives'])->name('index');
    Route::post('/', [IncentiveController::class, 'indexIncentives'])->name('index');
    Route::get('/create', [IncentiveController::class, 'createIncentive'])->name('create');
    Route::get('/date', [IncentiveController::class, 'dateIncentive'])->name('date');
    Route::get('/load', [IncentiveController::class, 'loadIncentive'])->name('load');
    Route::post('/store', [IncentiveController::class, 'storeIncentive'])->name('store');
    Route::get('/make', [IncentiveController::class, 'makeIncentive'])->name('make');
    Route::post('/action', [IncentiveController::class, 'actionIncentive'])->name('action');
    Route::post('/view', [IncentiveController::class, 'showIncentive'])->name('show');
    Route::post('/print', [IncentiveController::class, 'printIncentive'])->name('print');
    Route::get('/payment', [IncentiveController::class, 'payIncentive'])->name('payment');
    Route::get('/migrate', [AdminController::class, 'migrateIncentives'])->name('migrate');

    Route::prefix('payouts')->name('payouts.')->group(function () {
        Route::get('/receipt/create', [IncentiveController::class, 'createReceiptPayout'])->name('receipt.create');
        Route::post('/receipt/create', [IncentiveController::class, 'createReceiptPayout'])->name('receipt.create');
        Route::get('/bank/create', [IncentiveController::class, 'createBankPayout'])->name('bank.create');
        Route::post('/bank/create', [IncentiveController::class, 'createBankPayout'])->name('bank.create');
        Route::post('/bank/store', [IncentiveController::class, 'storeBankPayout'])->name('bank.store');
        Route::get('/bank/approve', [IncentiveController::class, 'approveBankPayoutList'])->name('bank.approve');
        Route::post('/bank/approve', [IncentiveController::class, 'approveBankPayout'])->name('bank.approve');
        Route::get('/bank/download', [IncentiveController::class, 'downloadBankPayment'])->name('bank.download');
        Route::post('/bank/update/status', [IncentiveController::class, 'updateBankPayoutStatus'])->name('bank.update.status');
    });

    Route::prefix('records')->name('records.')->group(function () {
        Route::get('/excel', [IncentiveController::class, 'indexBankPayments'])->name('excel.index');
        Route::post('/excel', [IncentiveController::class, 'indexBankPayments'])->name('excel.index');
        Route::post('/excel/view', [IncentiveController::class, 'showBankPayment'])->name('excel.show');
    });
});

Route::prefix('diesel-bills')->name('diesel-bills.')->group(function () {
    Route::prefix('entries')->name('entries.')->group(function () {
        Route::get('/',             [DieselBillController::class, 'indexDieselBill'])     -> middleware('permission:index_diesel_bill_entry')  -> name('index');
        Route::get('/create',       [DieselBillController::class, 'createDieselBill'])    -> middleware('permission:create_diesel_bill_entry') -> name('create');
        Route::post('/',            [DieselBillController::class, 'storeDieselBill'])     -> middleware('permission:create_diesel_bill_entry') -> name('store');
        Route::post('/show',        [DieselBillController::class, 'showDieselBill'])      -> middleware('permission:show_diesel_bill_entry')   -> name('show');
        Route::put('/{bill}',       [DieselBillController::class, 'updateDieselBill'])    -> middleware('permission:create_diesel_bill_entry') -> name('update');
        Route::delete('/{bill}',    [DieselBillController::class, 'destroyDieselBill'])   -> middleware('permission:create_diesel_bill_entry') -> name('destroy');
        Route::get('/{bill}/fetch', [DieselBillController::class, 'fetchDieselBill'])     -> middleware('permission:create_diesel_bill_entry') -> name('fetch');
        Route::get('/bill/pending', [DieselBillController::class, 'getPendingBills'])     -> middleware('permission:create_diesel_bill_entry') -> name('pending');
        Route::get('/vehicle/opening',[DieselBillController::class, 'getOpeningKilometer'])  -> middleware('permission:create_diesel_bill_entry') -> name('opening');
        Route::get('/accept',       [DieselBillController::class, 'indexDieselBillAccept'])  -> middleware('permission:accept_diesel_bill_entry') -> name('accept.index');
        Route::put('/accept/update',[DieselBillController::class, 'updateDieselBillAccept']) -> middleware('permission:accept_diesel_bill_entry') -> name('accept.update');
    });

    Route::prefix('statements')->name('statements.')->group(function () {
        Route::get('/',             [DieselBillController::class, 'indexBillStatement'])    -> middleware('permission:index_diesel_bill_statement')   -> name('index');
        Route::get('/create',       [DieselBillController::class, 'createBillStatement'])   -> middleware('permission:create_diesel_bill_statement')  -> name('create');
        Route::get('/load',         [DieselBillController::class, 'loadBillStatement'])     -> middleware('permission:create_diesel_bill_statement')  -> name('load');
        Route::post('/',            [DieselBillController::class, 'storeBillStatement'])    -> middleware('permission:create_diesel_bill_statement')  -> name('store');
        Route::post('/show',        [DieselBillController::class, 'showBillStatement'])     -> middleware('permission:show_diesel_bill_statement')    -> name('show');
        Route::get('/{stmt}/fetch', [DieselBillController::class, 'fetchBillStatement'])    -> middleware('permission:create_diesel_bill_statement')  -> name('fetch');
        Route::get('/date',         [DieselBillController::class, 'getDocumentDateForBunk']) -> middleware('permission:create_diesel_bill_statement') -> name('date');
        Route::get('/accept',       [DieselBillController::class, 'indexStatementAccept'])  -> middleware('permission:accept_diesel_bill_statement')  -> name('accept.index');
        Route::put('/accept',       [DieselBillController::class, 'updateStatementAccept']) -> middleware('permission:accept_diesel_bill_statement')  -> name('accept.update');
        Route::put('/accept/cancel',[DieselBillController::class, 'cancelStatementAccept']) -> middleware('permission:cancel_diesel_bill_statement')  -> name('accept.cancel');
    });

    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/request',      [DieselBillController::class, 'createPaymentRequest'])        -> middleware('permission:show_diesel_bill_payment_request') -> name('request.create');
        Route::post('/request',     [DieselBillController::class, 'storePaymentRequest'])         -> middleware('permission:show_diesel_bill_payment_request') -> name('request.store');
        Route::get('/approve',      [DieselBillController::class, 'createPaymentApproval'])       -> middleware('permission:show_diesel_bill_payment_approve') -> name('approve.create');
        Route::post('/approve',     [DieselBillController::class, 'approvePaymentApproval'])      -> middleware('permission:show_diesel_bill_payment_approve') -> name('approve.approve');
        Route::put('/approve',      [DieselBillController::class, 'updatePaymentApprovalStatus']) -> middleware('permission:show_diesel_bill_payment_approve') -> name('approve.status');
    });
});

Route::prefix('transport')->name('transport.')->group(function () {

    // ── Masters ──────────────────────────────────────────────────────────────

    // Vehicle Categories
    Route::prefix('vehicle-categories')->name('vehicle-categories.')->group(function () {
        Route::get('/',             [VehicleCategoryController::class, 'index'])  ->name('index');
        Route::get('/create',       [VehicleCategoryController::class, 'create']) ->name('create');
        Route::post('/',            [VehicleCategoryController::class, 'store'])  ->name('store');
        Route::get('/{vehicleCategory}',      [VehicleCategoryController::class, 'show'])    ->name('show');
        Route::get('/{vehicleCategory}/edit', [VehicleCategoryController::class, 'edit'])    ->name('edit');
        Route::put('/{vehicleCategory}',      [VehicleCategoryController::class, 'update'])  ->name('update');
        Route::delete('/{vehicleCategory}',   [VehicleCategoryController::class, 'destroy']) ->name('destroy');
    });

    // Vehicles (new transport module — separate from existing /vehicles route)
    Route::prefix('vehicles')->name('vehicles.')->group(function () {
        Route::get('/',             [VehicleController::class, 'index'])  ->name('index');
        Route::get('/create',       [VehicleController::class, 'create']) ->name('create');
        Route::post('/',            [VehicleController::class, 'store'])  ->name('store');
        Route::get('/{vehicle}',      [VehicleController::class, 'show'])    ->name('show');
        Route::get('/{vehicle}/edit', [VehicleController::class, 'edit'])    ->name('edit');
        Route::put('/{vehicle}',      [VehicleController::class, 'update'])  ->name('update');
        Route::delete('/{vehicle}',   [VehicleController::class, 'destroy']) ->name('destroy');
    });

    // Supplier Transporters
    Route::prefix('supplier-transporters')->name('supplier-transporters.')->group(function () {
        Route::get('/',             [SupplierTransporterController::class, 'index'])  ->name('index');
        Route::get('/create',       [SupplierTransporterController::class, 'create']) ->name('create');
        Route::post('/',            [SupplierTransporterController::class, 'store'])  ->name('store');
        Route::get('/{supplierTransporter}',      [SupplierTransporterController::class, 'show'])    ->name('show');
        Route::get('/{supplierTransporter}/edit', [SupplierTransporterController::class, 'edit'])    ->name('edit');
        Route::put('/{supplierTransporter}',      [SupplierTransporterController::class, 'update'])  ->name('update');
        Route::delete('/{supplierTransporter}',   [SupplierTransporterController::class, 'destroy']) ->name('destroy');
    });

    // Vehicle Route Mappings
    Route::prefix('vehicle-route-mappings')->name('vehicle-route-mappings.')->group(function () {
        Route::get('/',             [VehicleRouteMappingController::class, 'index'])  ->name('index');
        Route::get('/create',       [VehicleRouteMappingController::class, 'create']) ->name('create');
        Route::post('/',            [VehicleRouteMappingController::class, 'store'])  ->name('store');
        Route::get('/{vehicleRouteMapping}',      [VehicleRouteMappingController::class, 'show'])    ->name('show');
        Route::get('/{vehicleRouteMapping}/edit', [VehicleRouteMappingController::class, 'edit'])    ->name('edit');
        Route::put('/{vehicleRouteMapping}',      [VehicleRouteMappingController::class, 'update'])  ->name('update');
        Route::delete('/{vehicleRouteMapping}',   [VehicleRouteMappingController::class, 'destroy']) ->name('destroy');
    });

    // Vehicle Insurance
    Route::prefix('vehicle-insurance')->name('vehicle-insurance.')->group(function () {
        Route::get('/',             [VehicleInsuranceController::class, 'index'])  ->name('index');
        Route::get('/create',       [VehicleInsuranceController::class, 'create']) ->name('create');
        Route::post('/',            [VehicleInsuranceController::class, 'store'])  ->name('store');
        Route::get('/{vehicleInsurance}',      [VehicleInsuranceController::class, 'show'])    ->name('show');
        Route::get('/{vehicleInsurance}/edit', [VehicleInsuranceController::class, 'edit'])    ->name('edit');
        Route::put('/{vehicleInsurance}',      [VehicleInsuranceController::class, 'update'])  ->name('update');
        Route::delete('/{vehicleInsurance}',   [VehicleInsuranceController::class, 'destroy']) ->name('destroy');
    });

    // Vehicle Services
    Route::prefix('vehicle-services')->name('vehicle-services.')->group(function () {
        Route::get('/',             [VehicleServiceController::class, 'index'])  ->name('index');
        Route::get('/create',       [VehicleServiceController::class, 'create']) ->name('create');
        Route::post('/',            [VehicleServiceController::class, 'store'])  ->name('store');
        Route::get('/{vehicleService}',      [VehicleServiceController::class, 'show'])    ->name('show');
        Route::get('/{vehicleService}/edit', [VehicleServiceController::class, 'edit'])    ->name('edit');
        Route::put('/{vehicleService}',      [VehicleServiceController::class, 'update'])  ->name('update');
        Route::delete('/{vehicleService}',   [VehicleServiceController::class, 'destroy']) ->name('destroy');
    });

    // ── Transactions ─────────────────────────────────────────────────────────

    // Trip Sheets (Milk Collection)
    Route::prefix('trip-sheets')->name('trip-sheets.')->group(function () {
        Route::get('/',             [TripSheetController::class, 'index'])  ->name('index');
        Route::get('/create',       [TripSheetController::class, 'create']) ->name('create');
        Route::post('/',            [TripSheetController::class, 'store'])  ->name('store');
        Route::get('/{tripSheet}',      [TripSheetController::class, 'show'])    ->name('show');
        Route::get('/{tripSheet}/edit', [TripSheetController::class, 'edit'])    ->name('edit');
        Route::put('/{tripSheet}',      [TripSheetController::class, 'update'])  ->name('update');
        Route::delete('/{tripSheet}',   [TripSheetController::class, 'destroy']) ->name('destroy');
    });

    // Trip Sheets Market (Delivery)
    Route::prefix('trip-sheets-market')->name('trip-sheets-market.')->group(function () {
        Route::get('/',             [TripSheetMarketController::class, 'index'])  ->name('index');
        Route::get('/create',       [TripSheetMarketController::class, 'create']) ->name('create');
        Route::post('/',            [TripSheetMarketController::class, 'store'])  ->name('store');
        Route::get('/{tripSheetMarket}',      [TripSheetMarketController::class, 'show'])    ->name('show');
        Route::get('/{tripSheetMarket}/edit', [TripSheetMarketController::class, 'edit'])    ->name('edit');
        Route::put('/{tripSheetMarket}',      [TripSheetMarketController::class, 'update'])  ->name('update');
        Route::delete('/{tripSheetMarket}',   [TripSheetMarketController::class, 'destroy']) ->name('destroy');
    });

    // Transport Adjustments
    Route::prefix('transport-adjustments')->name('transport-adjustments.')->group(function () {
        Route::get('/',             [TransportAdjustmentController::class, 'index'])  ->name('index');
        Route::get('/create',       [TransportAdjustmentController::class, 'create']) ->name('create');
        Route::post('/',            [TransportAdjustmentController::class, 'store'])  ->name('store');
        Route::get('/{transportAdjustment}',      [TransportAdjustmentController::class, 'show'])    ->name('show');
        Route::get('/{transportAdjustment}/edit', [TransportAdjustmentController::class, 'edit'])    ->name('edit');
        Route::put('/{transportAdjustment}',      [TransportAdjustmentController::class, 'update'])  ->name('update');
        Route::delete('/{transportAdjustment}',   [TransportAdjustmentController::class, 'destroy']) ->name('destroy');
        Route::post('/{transportAdjustment}/approve', [TransportAdjustmentController::class, 'approve']) ->name('approve');
    });

    // Transport Bills
    Route::prefix('transport-bills')->name('transport-bills.')->group(function () {
        Route::get('/',             [TransportBillController::class, 'index'])  ->name('index');
        Route::get('/create',       [TransportBillController::class, 'create']) ->name('create');
        Route::post('/',            [TransportBillController::class, 'store'])  ->name('store');
        Route::get('/{transportBill}',      [TransportBillController::class, 'show'])    ->name('show');
        Route::get('/{transportBill}/edit', [TransportBillController::class, 'edit'])    ->name('edit');
        Route::put('/{transportBill}',      [TransportBillController::class, 'update'])  ->name('update');
        Route::delete('/{transportBill}',   [TransportBillController::class, 'destroy']) ->name('destroy');
        Route::post('/{transportBill}/approve', [TransportBillController::class, 'approve'])       ->name('approve');
        Route::post('/{transportBill}/payment', [TransportBillController::class, 'recordPayment']) ->name('payment');
        // AJAX: get unbilled trips for bill creation
        Route::get('/unbilled-trips', [TransportBillController::class, 'getUnbilledTrips']) ->name('unbilled-trips');
    });

    // Secondary Transport
    Route::prefix('secondary-transport')->name('secondary-transport.')->group(function () {
        Route::get('/',             [SecondaryTransportController::class, 'index'])  ->name('index');
        Route::get('/create',       [SecondaryTransportController::class, 'create']) ->name('create');
        Route::post('/',            [SecondaryTransportController::class, 'store'])  ->name('store');
        Route::get('/{secondaryTransport}',      [SecondaryTransportController::class, 'show'])    ->name('show');
        Route::get('/{secondaryTransport}/edit', [SecondaryTransportController::class, 'edit'])    ->name('edit');
        Route::put('/{secondaryTransport}',      [SecondaryTransportController::class, 'update'])  ->name('update');
        Route::delete('/{secondaryTransport}',   [SecondaryTransportController::class, 'destroy']) ->name('destroy');
        // AJAX: get unbilled records for bill creation
        Route::get('/unbilled-records', [SecondaryTransportController::class, 'getUnbilledRecords']) ->name('unbilled-records');
    });

    // Secondary Transport Bills
    Route::prefix('secondary-transport-bills')->name('secondary-transport-bills.')->group(function () {
        Route::get('/',             [SecondaryTransportBillController::class, 'index'])  ->name('index');
        Route::get('/create',       [SecondaryTransportBillController::class, 'create']) ->name('create');
        Route::post('/',            [SecondaryTransportBillController::class, 'store'])  ->name('store');
        Route::get('/{secondaryTransportBill}',      [SecondaryTransportBillController::class, 'show'])    ->name('show');
        Route::get('/{secondaryTransportBill}/edit', [SecondaryTransportBillController::class, 'edit'])    ->name('edit');
        Route::put('/{secondaryTransportBill}',      [SecondaryTransportBillController::class, 'update'])  ->name('update');
        Route::delete('/{secondaryTransportBill}',   [SecondaryTransportBillController::class, 'destroy']) ->name('destroy');
        Route::post('/{secondaryTransportBill}/approve', [SecondaryTransportBillController::class, 'approve'])       ->name('approve');
        Route::post('/{secondaryTransportBill}/payment', [SecondaryTransportBillController::class, 'recordPayment']) ->name('payment');
    });

    // Secondary Payment Abstracts
    Route::prefix('secondary-payment-abstracts')->name('secondary-payment-abstracts.')->group(function () {
        Route::get('/',             [SecondaryPaymentAbstractController::class, 'index'])  ->name('index');
        Route::get('/create',       [SecondaryPaymentAbstractController::class, 'create']) ->name('create');
        Route::post('/',            [SecondaryPaymentAbstractController::class, 'store'])  ->name('store');
        Route::get('/{secondaryPaymentAbstract}',      [SecondaryPaymentAbstractController::class, 'show'])    ->name('show');
        Route::get('/{secondaryPaymentAbstract}/edit', [SecondaryPaymentAbstractController::class, 'edit'])    ->name('edit');
        Route::put('/{secondaryPaymentAbstract}',      [SecondaryPaymentAbstractController::class, 'update'])  ->name('update');
        Route::delete('/{secondaryPaymentAbstract}',   [SecondaryPaymentAbstractController::class, 'destroy']) ->name('destroy');
        Route::post('/{secondaryPaymentAbstract}/finalise',    [SecondaryPaymentAbstractController::class, 'finalise'])    ->name('finalise');
        Route::post('/{secondaryPaymentAbstract}/recalculate', [SecondaryPaymentAbstractController::class, 'recalculate']) ->name('recalculate');
    });

});

// Downloads
Route::prefix('downloads')->name('downloads.')->group(function () {
    Route::prefix('excel')->name('excel.')->group(function () {
        Route::get('/bank/payment', [ExcelController::class, 'downloadBankPayments']) -> name('bank.payment');
    });
});

Route::get('/statement/pdf', [PdfController::class, 'generatePDF'])->name('statement.pdf');
//Route::post('/statement/pdf', [PdfController::class, 'generatePDF'])->name('statement.pdf');

// Expenses
Route::get('/expense/types', [WorkController::class, 'expenseTypes'])->name('expense.types');
Route::post('/expense/types/{id}', [WorkController::class, 'expenseTypesStore'])->name('expense.types.store');
Route::get('/expense/types/{id}', [WorkController::class, 'expenseTypesEdit'])->name('expense.types.edit');
Route::delete('/expense/delete/{id}', [WorkController::class, 'expenseTypesDestroy'])->name('expense.types.destroy');
Route::get('/expense/entry', [WorkController::class, 'expenseEntry'])->name('expense.entry');
Route::post('/expense/entry/store', [WorkController::class, 'expenseEntryStore'])->name('expense.entry.store');
Route::get('/closing/balance', [WorkController::class, 'closingBalance'])->name('closing.balance');
Route::post('/closing/balance', [WorkController::class, 'closingBalance'])->name('closing.balance');
Route::get('/expense/entry/list', [WorkController::class, 'expenseEntryList'])->name('expense.entry.list');
Route::post('/expense/entry/list', [WorkController::class, 'expenseEntryList'])->name('expense.entry.list');
Route::post('/expense/entry/view', [WorkController::class, 'expenseEntryView'])->name('expense.entry.view');
Route::get('/expense/entry/view', [WorkController::class, 'expenseEntryView'])->name('expense.entry.view');
Route::get('/expense/entry/edit', [WorkController::class, 'expenseEntryEdit'])->name('expense.entry.edit');
Route::post('/expense/entry/edit', [WorkController::class, 'expenseEntryEdit'])->name('expense.entry.edit');
Route::post('/expense/entry/update/{id}', [WorkController::class, 'expenseEntryUpdate'])->name('expense.entry.update');
Route::get('/expense/approval', [WorkController::class, 'expenseApproval'])->name('expense.approval');
Route::post('/expense/approval/store', [WorkController::class, 'expenseApprovalStore'])->name('expense.approval.store');
Route::get('/expense/payment', [WorkController::class, 'expensePayment'])->name('expense.payment');
Route::get('/expense/receipt', [WorkController::class, 'expenseReceipt'])->name('expense.receipt.list');
Route::post('/expense/receipt', [WorkController::class, 'expenseReceipt'])->name('expense.receipt.list');
Route::get('/expense/receipt/view', [WorkController::class, 'expenseReceiptView'])->name('expense.receipt.view');
Route::post('/expense/receipt/view', [WorkController::class, 'expenseReceiptView'])->name('expense.receipt.view');
Route::post('/expense/store/denomination', [WorkController::class, 'expenseStoreDenomination'])->name('expense.store-denomination');
Route::get('/expense/non/denomination', [WorkController::class, 'expenseNonDenomination'])->name('expense.non.denominatiion');

// Tally
Route::get('/tally-send', [TallyController::class, 'sendDataToTally']);
Route::get('/tally-receive', [TallyController::class, 'receiveDataFromTally']);
Route::get('/download-xml', [TallyController::class, 'downloadXml']);
Route::get('/automate-xml', [TallyController::class, 'automateXml'])->name('tally.automate');
Route::get('/tally-sync', [TallyController::class, 'createTallySync'])->name('tally.sync');
Route::post('/save-xml', [TallyController::class, 'saveXml'])->name('tally.save');
Route::get('/tally-invoices', [TallyController::class, 'tallyInvoices'])->name('tally.invoices');
Route::post('/tally-invoices', [TallyController::class, 'tallyInvoices'])->name('tally.invoices');
Route::get('/tally-invoice', [TallyController::class, 'tallyInvoice'])->name('tally.invoice');
Route::get('/tally-masters', [TallyController::class, 'tallyMasters'])->name('tally.masters');
Route::get('/tally-master', [TallyController::class, 'tallyMaster'])->name('tally.master');

Route::get('/tally-groups', [TallyController::class, 'tallyGroups'])->name('tally.groups');
Route::get('/tally-ledgers', [TallyController::class, 'tallyLedgers'])->name('tally.ledgers');
Route::get('/tally-units', [TallyController::class, 'tallyUnits'])->name('tally.units');
Route::get('/tally-stock-groups', [TallyController::class, 'tallyStockGroups'])->name('tally.stock-groups');
Route::get('/tally-stock-items', [TallyController::class, 'tallyStockItems'])->name('tally.stock-items');
Route::get('/tally-sync-invoice', [TallyController::class, 'tallySyncInvoice'])->name('tally.sync.invoice');
Route::get('/tally-sync-master', [TallyController::class, 'tallySyncMaster'])->name('tally.sync.master');

Route::get('/tally-address', [TallyController::class, 'getAddress']);

/* ----------------------------- Transactions End ----------------------------- */

/* ----------------------------- Data Explorer Start ----------------------------- */

Route::get('/price/explorer', [ExplorerController::class, 'priceExplorer'])->middleware('permission:index_price_table_explorer')->name('price.explorer');
Route::get('/tax/explorer', [ExplorerController::class, 'taxExplorer'])->middleware('permission:index_tax_table_explorer')->name('tax.explorer');
Route::post('/tax/explorer', [ExplorerController::class, 'taxExplorer'])->middleware('permission:index_tax_table_explorer')->name('tax.explorer');
Route::get('/gst/explorer', [ExplorerController::class, 'gstExplorer'])->middleware('permission:index_gst_type_explorer')->name('gst.explorer');
Route::post('/gst/explorer', [ExplorerController::class, 'gstExplorer'])->middleware('permission:index_gst_type_explorer')->name('gst.explorer');
Route::get('/tcs/explorer', [ExplorerController::class, 'tcsExplorer'])->middleware('permission:index_tcs_status_explorer')->name('tcs.explorer');
Route::post('/tcs/explorer', [ExplorerController::class, 'tcsExplorer'])->middleware('permission:index_tcs_status_explorer')->name('tcs.explorer');
Route::get('/tds/explorer', [ExplorerController::class, 'tdsExplorer'])->middleware('permission:index_tds_status_explorer')->name('tds.explorer');
Route::post('/tds/explorer', [ExplorerController::class, 'tdsExplorer'])->middleware('permission:index_tds_status_explorer')->name('tds.explorer');
Route::get('/payment/explorer', [ExplorerController::class, 'paymentExplorer'])->middleware('permission:index_payment_mode_explorer')->name('payment.explorer');
Route::post('/payment/explorer', [ExplorerController::class, 'paymentExplorer'])->middleware('permission:index_payment_mode_explorer')->name('payment.explorer');
Route::get('/incentive/explorer', [ExplorerController::class, 'incentiveExplorer'])->middleware('permission:index_incentive_mode_explorer')->name('incentive.explorer');
Route::post('/incentive/explorer', [ExplorerController::class, 'incentiveExplorer'])->middleware('permission:index_incentive_mode_explorer')->name('incentive.explorer');
Route::get('/price/list', [ExplorerController::class, 'priceListExplorer'])->middleware('permission:index_price_list_explorer')->name('price.list');
Route::post('/price/list', [ExplorerController::class, 'priceListExplorer'])->middleware('permission:index_price_list_explorer')->name('price.list');
Route::get('/customer/price', [ExplorerController::class, 'customerPriceExplorer'])->middleware('permission:index_customer_price_explorer')->name('customer.price');
Route::post('/customer/price', [ExplorerController::class, 'customerPriceExplorer'])->middleware('permission:index_customer_price_explorer')->name('customer.price');
Route::get('/price/varient', [ExplorerController::class, 'priceVariantExplorer'])->middleware('permission:index_product_price_explorer')->name('price.variant');
Route::post('/price/varient', [ExplorerController::class, 'priceVariantExplorer'])->middleware('permission:index_product_price_explorer')->name('price.variant');

// Cash Register
Route::prefix('cash-register')->name('cash.register.')->group(function () {
    Route::get('/', [ExplorerController::class, 'cashRegister'])->middleware('permission:show_cash_register');
    Route::get('/get', [ExplorerController::class, 'getCashRegister'])->middleware('permission:show_cash_register')->name('get');
    Route::get('/re-register', [ExplorerController::class, 'regenerateCashRegister'])->middleware('permission:show_cash_register')->name('re-register');
});

Route::prefix('payments')->name('payments.')->group(function () {
    Route::get('/',      [ExplorerController::class, 'indexBankPayment']) -> middleware('permission:show_bank_payment') -> name('index');
    Route::post('/view', [ExplorerController::class, 'showBankPayment'])  -> middleware('permission:show_bank_payment') -> name('show');
});

/* ----------------------------- Data Explorer End ----------------------------- */


/* ----------------------------- Reports Start ----------------------------- */

Route::get('/report/enquiry', [ReportController::class, 'enquiryReport'])->name('report.enquiry');
Route::post('/report/enquiry', [ReportController::class, 'enquiryReport'])->name('report.enquiry');
Route::get('/export/enquiry', [ExportController::class, 'enquiryExport'])->name('export.enquiry');
Route::get('/report/attendance', [ReportController::class, 'attendanceReport'])->name('report.attendance');
Route::post('/report/attendance', [ReportController::class, 'attendanceReport'])->name('report.attendance');
Route::get('/export/attendance', [ExportController::class, 'attendanceExport'])->name('export.attendance');

Route::get('/report/sales/item-wise', [ReportController::class, 'itemWiseSalesReport'])->name('report.sales.item-wise')->middleware('permission:show_item_wise_report');
Route::post('/report/sales/item-wise', [ReportController::class, 'itemWiseSalesReport'])->name('report.sales.item-wise')->middleware('permission:show_item_wise_report');
Route::get('/export/sales/item-wise', [ExportController::class, 'itemWiseSalesExport'])->name('export.sales.item-wise')->middleware('permission:show_item_wise_report');
Route::get('/report/sales/hsn-wise', [ReportController::class, 'hsnWiseSalesReport'])->name('report.sales.hsn-wise')->middleware('permission:show_hsn_wise_report');
Route::post('/report/sales/hsn-wise', [ReportController::class, 'hsnWiseSalesReport'])->name('report.sales.hsn-wise')->middleware('permission:show_hsn_wise_report');
Route::get('/export/sales/hsn-wise', [ExportController::class, 'hsnWiseSalesExport'])->name('export.sales.hsn-wise')->middleware('permission:show_hsn_wise_report');
Route::get('/report/sales/tax-wise', [ReportController::class, 'taxWiseSalesReport'])->name('report.sales.tax-wise')->middleware('permission:show_tax_wise_report');
Route::post('/report/sales/tax-wise', [ReportController::class, 'taxWiseSalesReport'])->name('report.sales.tax-wise')->middleware('permission:show_tax_wise_report');
Route::get('/export/sales/tax-wise', [ExportController::class, 'taxWiseSalesExport'])->name('export.sales.tax-wise')->middleware('permission:show_tax_wise_report');
Route::get('/report/customer/item-wise', [ReportController::class, 'itemWiseCustomerReport'])->name('report.customer.item-wise')->middleware('permission:show_item_wise_customer_report');
Route::post('/report/customer/item-wise', [ReportController::class, 'itemWiseCustomerReport'])->name('report.customer.item-wise')->middleware('permission:show_item_wise_customer_report');
Route::get('/export/customer/item-wise', [ExportController::class, 'itemWiseCustomerExport'])->name('export.customer.item-wise')->middleware('permission:show_item_wise_customer_report');
Route::get('/report/item/customer-wise', [ReportController::class, 'customerWiseItemReport'])->name('report.item.customer-wise')->middleware('permission:show_customer_wise_item_report');
Route::post('/report/item/customer-wise', [ReportController::class, 'customerWiseItemReport'])->name('report.item.customer-wise')->middleware('permission:show_customer_wise_item_report');
Route::get('/export/item/customer-wise', [ExportController::class, 'customerWiseItemExport'])->name('export.item.customer-wise')->middleware('permission:show_customer_wise_item_report');

Route::get('/report/customer/account', [ReportController::class, 'accountStyleCustomerReport'])->middleware('permission:show_customer_account_report')->name('report.customer.account');
Route::post('/report/customer/account', [ReportController::class, 'accountStyleCustomerReport'])->middleware('permission:show_customer_account_report')->name('report.customer.account');
Route::get('/export/customer/account', [ExportController::class, 'customerAccountExport'])->middleware('permission:show_customer_account_report')->name('export.customer.account');

Route::get('/report/customer/statement', [ReportController::class, 'statementStyleCustomerReport'])->middleware('permission:show_customer_statement_report')->name('report.customer.statement');
Route::post('/report/customer/statement', [ReportController::class, 'statementStyleCustomerReport'])->middleware('permission:show_customer_statement_report')->name('report.customer.statement');
Route::get('/export/customer/statement', [ExportController::class, 'statementStyleCustomerExport'])->middleware('permission:show_customer_statement_report')->name('export.customer.statement');

Route::get('/report/invoice', [ReportController::class, 'invoiceReport'])->middleware('permission:show_invoice_report')->name('report.invoice');
Route::post('/report/invoice', [ReportController::class, 'invoiceReport'])->middleware('permission:show_invoice_report')->name('report.invoice');
Route::get('/export/invoice', [ExportController::class, 'invoiceExport'])->middleware('permission:show_invoice_report')->name('export.invoice');

Route::get('/report/day-wise', [ReportController::class, 'dayWiseReport'])->middleware('permission:show_day_wise_report')->name('report.day-wise');
Route::post('/report/day-wise', [ReportController::class, 'dayWiseReport'])->middleware('permission:show_day_wise_report')->name('report.day-wise');
Route::get('/export/day-wise', [ExportController::class, 'dayWiseExport'])->middleware('permission:show_day_wise_report')->name('export.day-wise');

Route::get('/report/transaction', [ReportController::class, 'transactionReport'])->middleware('permission:show_transaction_report')->name('report.transaction');
Route::post('/report/transaction', [ReportController::class, 'transactionReport'])->middleware('permission:show_transaction_report')->name('report.transaction');
Route::get('/export/transaction', [ExportController::class, 'transactionExport'])->middleware('permission:show_transaction_report')->name('export.transaction');

Route::prefix('reports')->name('reports.')->group(function () {
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::match(['get', 'post'],'/zero-value', [ReportController::class, 'zeroValueItemsReport'])->middleware('permission:show_zero_value_items_report')->name('zero-value');        
        Route::match(['get', 'post'],'/business-wise', [ReportController::class, 'businessWiseReport'])->middleware('permission:show_b2b_b2c_report')->name('business-wise');        
    });

    Route::match(['get', 'post'],'/bill-wise', [ReportController::class, 'billWiseReport'])->middleware('permission:show_bill_wise_report')->name('bill-wise');
    Route::match(['get', 'post'],'/document', [ReportController::class, 'documentReport'])->middleware('permission:show_document_summary_report')->name('document');
    Route::get('/document-detail', [ReportController::class, 'documentDetail'])->middleware('permission:show_document_summary_report')->name('document.detail');
});

Route::prefix('exports')->name('exports.')->group(function () {
    Route::get('/zero-value', [ExportController::class, 'zeroValueItemsExport'])->middleware('permission:show_zero_value_items_report')->name('zero-value');
    Route::get('/business-wise', [ExportController::class, 'businessWiseExport'])->middleware('permission:show_b2b_b2c_report')->name('business-wise');
});

/* ----------------------------- Reports End ----------------------------- */

Route::get('/receivables/{customer_id}', [CommonController::class, 'getReceivables'])->name('receivables');

Route::get('/opening-cash', [MasterController::class, 'createOpeningCash'])->name('openings.cash');
Route::post('/opening-cash', [MasterController::class, 'openingCash'])->name('openings.cash');
Route::get('/db_backup', [AdminController::class, 'backupDatabase'])->name('backup_db');

Route::get('/update-json-column', [AdminController::class, 'updateJsonColumn']);
Route::get('/execute-query', [AdminController::class, 'executeQuery']);
Route::get('/gst', [AdminController::class, 'gstUpdate']);

Route::view('/progress', 'tools.under_progress')->name('progress');
Route::view('/test-map', 'test.map3')->name('test.map');

Route::get('/test', [MasterController::class, 'test']);
Route::get('/testing', [InvoiceController::class, 'getPriceList2']);
Route::get('/clear-route', [AdminController::class, 'clearRoute']);
Route::get('/clear-cache', [AdminController::class, 'clearCache']);
Route::get('/clear-cache', [AdminController::class, 'clearCache']);
Route::get('/list-permissions', [AdminController::class, 'listPermissions']);
Route::get('/report-test', [ReportController::class, 'testFunction']);

Route::get('/receipts/modify', [AdminController::class, 'modifyReceipts']);
Route::get('/format-json', [AdminController::class, 'formatPriceMasterJsonData']);

Route::get('/task/price-masters', [TaskController::class, 'createPriceMasters']);

// Render perticular view file by foldername and filename and all passed in only one controller at a time
Route::get('{folder}/{file}', [MetricaController::class, 'indexWithOneFolder']);
// Render when Route Have 2 folder
Route::get('{folder1}/{folder2}/{file}', [MetricaController::class, 'indexWithTwoFolder']);

Route::get('/app-version', function () {
    return app()->version();
});

Route::get('/adjust-price', function () {
    \Artisan::call('price:process-adjustments');
    echo 'Prices adjusted Successfully';
});

Route::get('/schedule-price', function () {
    \Artisan::call('price:activate-scheduled');
    echo 'Price masters adjusted Successfully';
});

Route::get('/my-symlink', function(){
  $target = storage_path('app/public');
  $link = $_SERVER['DOCUMENT_ROOT'].'/mystorage';
//   echo $target . "<br>" .$link;
  symlink($target, $link);
  echo "symbolic link created successfully";
});

/*
Route::get('/posts',            [PostController::class, 'indexPost'])   -> name('posts.index');
Route::get('/posts/create',     [PostController::class, 'createPost'])  -> name('posts.create');
Route::post('/posts',           [PostController::class, 'storePost'])   -> name('posts.store');
Route::get('/posts/{id}',       [PostController::class, 'showPost'])    -> name('posts.show');
Route::get('/posts/{id}/edit',  [PostController::class, 'editPost'])    -> name('posts.edit');
Route::put('/posts/{id}',       [PostController::class, 'updatePost'])  -> name('posts.update');
Route::delete('/posts/{id}',    [PostController::class, 'destroyPost']) -> name('posts.destroy');


Route::prefix('posts')->name('posts.')->group(function () { 
    Route::get('/',          [PostController::class, 'indexPost'])   -> name('index');
    Route::get('/create',    [PostController::class, 'createPost'])  -> name('create');
    Route::post('/',         [PostController::class, 'storePost'])   -> name('store');
    Route::get('/{id}',      [PostController::class, 'showPost'])    -> name('show');
    Route::get('/{id}/edit', [PostController::class, 'editPost'])    -> name('edit');
    Route::put('/{id}',      [PostController::class, 'updatePost'])  -> name('update');
    Route::delete('/{id}',   [PostController::class, 'destroyPost']) -> name('destroy');
});
*/
