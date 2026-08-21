<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\SuperAdmin\BackupController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AutoLoginController;
use App\Http\Controllers\CronController;
use App\Http\Controllers\DemoRequestController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\PropertyController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PythonAuth;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\ExtraController;
use App\Http\Controllers\NightAuditlogController;
use App\Http\Controllers\BookingFollowUp;
use App\Http\Controllers\HrpayrollsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\FeedbackMasterController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\HappyhourController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CheckRegister;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\HouseKeeping;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\Cron\CleanUp;
use App\Http\Controllers\Cron\DatabaseSend;
use App\Http\Controllers\DeveloperTools;
use App\Http\Controllers\MainSetup\PointOfSale\RewardParameterC;
use App\Http\Controllers\TestingControlller;
use App\Http\Controllers\Tools\ToolsController;
use App\Http\Controllers\HkQrLoginController;
use App\Models\FeedbackMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


// test welcome route
Route::get('/welcome', function () {
    return view('welcome');
});
Route::get('/autochargepost', [CronController::class, 'autoCharge']);
Route::get('/run-db-backup', [DatabaseSend::class, 'run']);
Route::get('/cleanup-backups', [CleanUp::class, 'cleanup']);
Route::get('/', [HomeController::class, 'index']);
// qr generate string
Route::get('/generateqr', [TestingControlller::class, 'generateQr']);
Route::get('/delay/{seconds}', [TestingControlller::class, 'delayedResult']);

Route::get('camera', function () {
    return view('camera');
});
Route::get('camera2', function () {
    return view('camera2');
});
Route::get('loader', function () {
    return view('property.layouts.loader');
});
// Route::get('login', [HomeController::class, 'login']);
Route::post('/loginpy', [PythonAuth::class, 'login'])->middleware('throttle:5,1');
Auth::routes();

Route::get('my-tickets', [CompanyController::class, 'myTickets'])->name('tools.myTickets');
Route::get('my-ticket-messages', [CompanyController::class, 'getMyTicketMessages'])->name('tools.getMyTicketMessages');
Route::post('my-ticket-messages/send', [CompanyController::class, 'sendMyTicketMessage'])->name('tools.sendMyTicketMessage');
Route::post('my-ticket-messages/edit', [CompanyController::class, 'editMyTicketMessage'])->name('tools.editMyTicketMessage');
Route::get('my-ticket-notifications', [CompanyController::class, 'getMyTicketNotifications'])->name('tools.getMyTicketNotifications');
Route::post('my-ticket-confirm-solved', [CompanyController::class, 'confirmMyTicketSolved'])->name('tools.confirmMyTicketSolved');

Route::get('/storage-link', function () {
    $target_folder = storage_path('app/public');
    $link_folder = public_path('storage');
    if (!file_exists($link_folder)) {
        symlink($target_folder, $link_folder);
    }
});

// Route to serve storage files directly
Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);

    if (!file_exists($fullPath) || is_dir($fullPath)) {
        abort(404);
    }

    $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';
    $headers = [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
    ];

    return response()->file($fullPath, $headers);
})->where('path', '.*');

// routes/web.php
Route::post('/auto-login', [AutoLoginController::class, 'loginUser'])->name('auto.login')->middleware('throttle:10,1');

Route::get('application', [HomeController::class, 'application'])->name('application');
// Open Api Usages Page
Route::get('apiusages', [HomeController::class, 'apiusages'])->name('api.usages');
// Redirects to Admin Home
Route::get('superadmin', [MainController::class, 'index'])->name('superadmin')->middleware('superadmin');
// All Tickets for Super Admin
Route::get('superadmin/tickets', [ToolsController::class, 'allTickets'])->name('superadmin.tickets')->middleware('superadmin');

// Superadmin dynamic pages management
Route::get('superadmin/my-pages', [\App\Http\Controllers\SuperAdmin\SuperAdminMainController::class, 'myPages'])
    ->name('superadmin.my-pages')
    ->middleware('superadmin');
Route::get('superadmin/my-pages/create', [\App\Http\Controllers\SuperAdmin\SuperAdminMainController::class, 'createPage'])
    ->name('superadmin.pages.create')
    ->middleware('superadmin');
Route::post('superadmin/my-pages', [\App\Http\Controllers\SuperAdmin\SuperAdminMainController::class, 'storePage'])
    ->name('superadmin.pages.store')
    ->middleware('superadmin');
Route::get('superadmin/my-pages/{id}/edit', [\App\Http\Controllers\SuperAdmin\SuperAdminMainController::class, 'editPage'])
    ->name('superadmin.pages.edit')
    ->middleware('superadmin');
Route::put('superadmin/my-pages/{id}', [\App\Http\Controllers\SuperAdmin\SuperAdminMainController::class, 'updatePage'])
    ->name('superadmin.pages.update')
    ->middleware('superadmin');
Route::delete('superadmin/my-pages/{id}', [\App\Http\Controllers\SuperAdmin\SuperAdminMainController::class, 'destroyPage'])
    ->name('superadmin.pages.destroy')
    ->middleware('superadmin');

Route::get('admin/activity-logs', [ActivityLogController::class, 'index'])
    ->name('admin.activity.logs')
    ->middleware('superadmin');
Route::get('admin/activity-logs/data', [ActivityLogController::class, 'data'])
    ->name('admin.activity.logs.data')
    ->middleware('superadmin');
Route::get('admin/activity-logs/top-routes', [ActivityLogController::class, 'getTopRoutes'])
    ->name('admin.activity.logs.top.routes')
    ->middleware('superadmin');
Route::get('admin/activity-logs/top-users', [ActivityLogController::class, 'getTopUsers'])
    ->name('admin.activity.logs.top.users')
    ->middleware('superadmin');
// Logout Admin
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
// Company Register Page
Route::get('/companyreg', [MainController::class, 'companyregister']);
// Load State
Route::post('/getState', [MainController::class, 'getState']);
//Check Mobile
Route::post('/check_mobile', [MainController::class, 'check_mobile']);
//Check Email
Route::post('/check_email', [MainController::class, 'check_email']);
//Check Username
Route::post('/check_username', [MainController::class, 'check_username']);
// Check sn_num
Route::post('/check_sn_num', [MainController::class, 'check_Sno']);
// Submit Company Registration Form
Route::post('companystore', [MainController::class, 'store'])->name('companystore');
// Open Company List
Route::get('/companylist', [MainController::class, 'loadcompanylist']);
// Open Developer Tools
Route::get('developertools', [DeveloperTools::class, 'opendevelopertools'])->name('developertools');
// Generate API Key
Route::post('generate-api-client', [DeveloperTools::class, 'generate'])->name('api.client.generate');
// Download Api Excel
Route::get('download-api-client/{propertyid}', [DeveloperTools::class, 'download'])->name('api.client.download');
// Update property
Route::get('updatepropertyadmin', [MainController::class, 'openUpdateProperty'])->name('propertyid');
// Disable Property
Route::get('disablepropertyadmin', [MainController::class, 'Disablepropertyadmin'])->name('propertyid');
// Enable Property
Route::get('enablepropertyadmin', [MainController::class, 'enableproperty'])->name('propertyid');
// Company Update
Route::post('/updatingproperty', [MainController::class, 'companyupdate'])->name('company.update');
//City Form
Route::get('/cityform', [MainController::class, 'opencity']);
// Check City name
Route::post('/check_city_name', [MainController::class, 'check_city_name']);
//Check zipcode
Route::post('/check_zipcode', [MainController::class, 'check_zipcode']);
// Load State2
Route::post('/getStateadmin', [MainController::class, 'getStateadmin']);
// Submit City Form
Route::post('citystore', [MainController::class, 'submitcity'])->name('citystore');
// State Form
route::get('/stateform', [MainController::class, 'openstate']);
// Country Form
Route::post('/check_state_insert', [MainController::class, 'check_state_insert']);
//Check state code
Route::post('/check_state_code', [MainController::class, 'check_state_code']);
// Submit State Form
route::post('statestore', [MainController::class, 'submitstate'])->name('statestore');
// Check Country name
Route::post('/check_country', [MainController::class, 'check_country']);
//Check country_code
Route::post('/check_country_code', [MainController::class, 'check_country_code']);
// Country Form
route::get('/countryform', [MainController::class, 'opencountry']);
// Submit Country Form
route::post('countrystore', [MainController::class, 'submitcountry'])->name('countrystore');
// Property Login
Route::get('company', [PropertyController::class, 'loadProperty'])->name('company')->middleware('company');
// Update Country Form Open
route::get('updatecountryadmin', [MainController::class, 'updatecountry']);
// Update Country
route::post('update_countrystore', [MainController::class, 'update_countrystore'])->name('update_countrystore');
// Update state Form Open
route::get('updatestateformadmin', [MainController::class, 'updatestate']);
// Update state
route::post('statestoreupdate', [MainController::class, 'update_statestore'])->name('statestoreupdate');
// Update City Form Open
route::get('updatecityformadmin', [MainController::class, 'updatecity']);
// Update City
route::post('citystoreupdate', [MainController::class, 'citystoreupdate'])->name('citystoreupdate');
// Open Userlist
route::get('/userlist', [MainController::class, 'loaduserlist']);
// Disable User
Route::get('disableusermaster2', [MainController::class, 'disableusermaster'])->name('id');
// Enable User
Route::get('enableusermaster2', [MainController::class, 'enableusermaster'])->name('id');
// Open User Master Form
route::get('/usermasteradmin', [MainController::class, 'openusermaster']);
// Update User Master Form Open
route::get('updateusermaster2', [MainController::class, 'updateusermaster']);
// Update User Master
route::post('update_usermaster2', [MainController::class, 'update_usermasterstore'])->name('update_usermaster2');
// Submit User Master Form
route::post('usermasterstore2', [MainController::class, 'submitusermaster'])->name('usermasterstore2');
// Update User AP from User Master list
route::post('update_user_ap2', [MainController::class, 'updateUserAp'])->name('update_user_ap2');
// Search Username
route::match(['get', 'post'], 'searchusername', [MainController::class, 'loadcompanylist'])->name('searchusername');
// Open QR Generate Page
Route::get('superadmin/qrgenerate', [MainController::class, 'openqrgenerate']);
// QR Generate Process
Route::post('superadmin/qrgenerate', [\App\Http\Controllers\SuperAdmin\QRGenerate::class, 'generateQR'])->name('admin.qr.generate');
// Get Update Logs
Route::get('getUpdateLogs', [MainController::class, 'fetchUpdates']);
// routes/web.php
// Open Expiry Module
Route::get('expirymodule', [MainController::class, 'showUpdateForm']);
Route::post('/property/update-expiry', [PropertyController::class, 'updateExpiry'])->name('property.updateExpiry');
Route::get('/get-expiry-data/{propertyid}', [PropertyController::class, 'getExpiryData'])->name('property.getExpiryData');
// Open Backup Page
Route::get('superadmin/backups', [BackupController::class, 'index'])->name('superadmin.backups');
// Download Backup Prepare
Route::get('superadmin/storagefdownload', [BackupController::class, 'downloadStorage'])->name('superadmin.storagefdownload');
// Download Created Backup 
Route::get('superadmin/download-temp-zip/{filename}', [BackupController::class, 'downloadTempZip']);
// Download Database
Route::POST('superadmin/database-backup', [BackupController::class, 'downloadDatabaseBackup'])->name('superadmin.database-backup');
// Verify Database
Route::post('superadmin/verify-database', [BackupController::class, 'verifyDatabase'])->name('superadmin.verify-database');
Route::get('/test-exec', [BackupController::class, 'testExec']);
Route::get('/test-dump', [BackupController::class, 'testDump']);
// About Us Open
Route::get('about', [HomeController::class, 'about'])->name('about');
// Front Office Services
Route::get('services/front-office', [HomeController::class, 'frontofficeservices'])->name('services.front-office');
// POS Services
Route::get('services/pointofsale', [HomeController::class, 'pointofsaleservices'])->name('services.pointofsale');
// Banquet Services
Route::get('services/banquet', [HomeController::class, 'banquetservices'])->name('services.banquet');
// Inventory Services
Route::get('services/inventory', [HomeController::class, 'inventoryservices'])->name('services.inventory');
// Reservation Services
Route::get('services/reservation', [HomeController::class, 'reservationservices'])->name('services.reservation');
// Reservation Developer
Route::get('developer/reservation', [HomeController::class, 'reservationdeveloper'])->name('developer.reservation');
// Open Contact Page
Route::get('contact', [HomeController::class, 'contact'])->name('contact');

// Dynamic content page (slug from pages table)
Route::get('page/{slug}', [HomeController::class, 'dynamicPage'])->name('page.show');
// Submit Contact Form
Route::post('contactsubmit', [ContactController::class, 'store'])->name('contact.submit');
// Submit Demo Request
Route::post('/demo-request', [DemoRequestController::class, 'store'])->name('demo-request.store');

//////////////////// Deepak Routes ////////////////////////

// Booking Follow Up
Route::post('/booking-followup', [BookingFollowUp::class, 'store'])->name('booking-followup.store');

Route::middleware(['company'])->group(function () {
    Route::get('inquiryfollup', [BookingFollowUp::class, 'index'])->name('inquiryfollup');
    Route::get('get-inquiryfollup', [BookingFollowUp::class, 'data'])->name('get-inquiryfollup');
});

Route::get('/booking-followup/comments/{inqno}', [BookingFollowUp::class, 'comments'])->name('bookingfollowup.comments');

Route::get('dailyfunctionsheet', [ReportController::class, 'dailyFunctionSheet'])->name('dailyfunctionsheet');
Route::post('dailyfunctionsheetdata', [ReportController::class, 'dailyFunctionSheetData'])->name('dailyfunctionsheetdata');
Route::get('bookinginquirydetail', [ReportController::class, 'bookingEnquiryDetail'])->name('bookinginquirydetail');
Route::post('bookinginquirydetailfetch', [ReportController::class, 'bookingEnquiryDetailFetch'])->name('bookinginquirydetailfetch');

Route::get('outStandingreport', [ReportController::class, 'outStandingreport'])->name('outStandingreport');
Route::post('outStandingreportdata', [ReportController::class, 'outStandingreportData'])->name('outStandingreportdata');


Route::get('companywisesalereport', [ReportController::class, 'companyWiseSaleReport'])->name('companywisesalereport');
Route::post('companywisesalereportdata', [ReportController::class, 'companyWiseSaleReportData'])->name('companywisesalereportdata');


Route::get('itemwisesalesreport', [ReportController::class, 'itemWiseSaleReport'])->name('itemwisesalesreport');
Route::post('itemwisesalesreportdata', [ReportController::class, 'itemWiseSaleReportData'])->name('itemwisesalesreportdata');

Route::get('cashierreports', [ReportController::class, 'payChargeReport'])->name('cashierreports');
Route::post('paychargereportdata', [ReportController::class, 'payChargeReportData'])->name('paychargereportdata');

Route::get('taxreport', [ReportController::class, 'taxReport'])->name('taxreport');
Route::post('getAlltaxCodes', [ReportController::class, 'getAlltaxCodes'])->name('getAlltaxCodes');
Route::post('taxreportdata', [ReportController::class, 'taxReportData'])->name('taxreportdata');


/////////////////// HR Payroll Routes ////////////////////////
// Get all Designation
Route::get('designation', [HrpayrollsController::class, 'designation'])->name('designation');
Route::get('designationdata', [HrpayrollsController::class, 'designationData'])->name('designationdata');
// Add Designation
Route::post('adddesignation', [HrpayrollsController::class, 'addDesignation'])->name('adddesignation');
// Edit Designation
Route::post('editdesignation', [HrpayrollsController::class, 'editDesignation'])->name('editdesignation');
// Delete Designation
Route::post('deletedesignation', [HrpayrollsController::class, 'deleteDesignation'])->name('deletedesignation');
// Get all Designation CSV //////////////////
Route::get('designationexport', [HrpayrollsController::class, 'designationExport'])->name('designationexport');
// Import Designation from JSON //////////////////
Route::get('designationimport', [HrpayrollsController::class, 'designationImport'])->name('designationimport');


// Get all Employees Category
Route::get('employeecategory', [HrpayrollsController::class,   'empCategory'])->name('employeecategory');
Route::get('employeecategorydata', [HrpayrollsController::class,   'empCategoryData'])->name('employeecategorydata');
// Add Employees Category
Route::post('addemployeecategory', [HrpayrollsController::class,   'addEmpCategory'])->name('addemployeecategory');
// Edit Employees Category
Route::post('editemployeecategory', [HrpayrollsController::class,   'editEmpCategory'])->name('editemployeecategory');
// Delete Employees Category
Route::post('deleteemployeecategory', [HrpayrollsController::class,   'deleteEmpCategory'])->name('deleteemployeecategory');

// Get all Employees CSV
Route::get('employeeexport', [HrpayrollsController::class, 'employeeExport'])->name('employeeexport');
// Import Employees from JSON
Route::get('employeeimport', [HrpayrollsController::class, 'employeeImport'])->name('employeeimport');


// Get all Employees
Route::get('empolyee', [HrpayrollsController::class, 'employee'])->name('empolyee');
Route::get('employeedata', [HrpayrollsController::class, 'employeeData'])->name('employeedata');
Route::post('getAccountDetails', [HrpayrollsController::class, 'getAccountDetails'])->name('getAccountDetails');
// Add Employees
Route::post('addemployee', [HrpayrollsController::class, 'addEmployee'])->name('addemployee');
// Edit Employees
Route::get('employeeedit/{id}', [HrpayrollsController::class, 'employeeEdit'])->name('employeeedit');
Route::post('editemployee', [HrpayrollsController::class, 'updateEmployee'])->name('editemployee');
// Delete Employees
Route::post('deleteemployee', [HrpayrollsController::class, 'deleteEmployee'])->name('deleteemployee');
// Get all Employees CSV
Route::get('allemployeeexport', [HrpayrollsController::class, 'allEmployeeExport'])->name('allemployeeexport');


// Get Happy Hours
Route::get('happyhours', [HappyhourController::class, 'happyHours'])->name('happyhours');
Route::get('happyhoursdata', [HappyhourController::class, 'happyHoursData'])->name('happyhoursdata');
// Add Happy Hours
Route::post('addhappyhours', [HappyhourController::class, 'addHappyHours'])->name('addhappyhours');
// Edit Happy Hours
Route::post('edithappyhours', [HappyhourController::class, 'editHappyHours'])->name('edithappyhours');
// Delete Happy Hours
Route::post('deletehappyhours', [HappyhourController::class, 'deleteHappyHours'])->name('deletehappyhours');
// Get Happy Hours CSV
Route::get('happyhourexport', [HappyhourController::class, 'happyHoursExport'])->name('happyhourexport');
Route::post('getoutlet', [HappyhourController::class, 'getoutlet'])->name('getoutlet');



/////////////////////// Maintenance ////////////////

// Get Location
Route::get('locationmaster', [MaintenanceController::class, 'location'])->name('locationmaster');
Route::get('printlocationmaster', [MaintenanceController::class, 'printLocationMaster'])->name('printlocationmaster');
Route::get('locationmaster/export', [MaintenanceController::class, 'exportLocationMaster'])->name('locationmaster.export');
// Add Location
Route::post('addlocation', [MaintenanceController::class, 'addLocation'])->name('addlocation');
// Edit Location
Route::post('editlocation', [MaintenanceController::class, 'editLocation'])->name('editlocation');
// Delete Location
Route::post('deletelocation', [MaintenanceController::class, 'deleteLocation'])->name('deletelocation');


////// Assets

// Get Assets
Route::get('assetsmaster', [MaintenanceController::class, 'assets'])->name('assetsmaster');
Route::get('asstesdata', [MaintenanceController::class, 'assetsData'])->name('asstesdata');
// Add Assets
Route::post('getshortCode', [MaintenanceController::class, 'getShortNameAndCode'])->name('getshortCode');
Route::post('getCode', [MaintenanceController::class, 'getCode'])->name('getCode');
Route::post('addassets', [MaintenanceController::class, 'addAssets'])->name('addassets');
// Edit Assets
Route::post('editassets', [MaintenanceController::class, 'editAssets'])->name('editassets');
// Delete Assets
Route::post('deleteassets', [MaintenanceController::class, 'deleteAssets'])->name('deleteassets');
// Get Assets CSV
Route::get('assetsexport', [MaintenanceController::class, 'assetsExport'])->name('assetsexport');



////////////////////////// Extra Reward Points ///////////////////
Route::get('rewardparameter', [RewardParameterC::class, 'rewardpoints'])->name('rewardparameter');
Route::get('rewardpointsdata', [RewardParameterC::class, 'rewardpointsData'])->name('rewardpointsdata');

// Add Reward Points
Route::post('addrewardpoints', [RewardParameterC::class, 'addRewardPoints'])->name('addrewardpoints');
// Edit Reward Points
Route::post('updateRewardPoints', [RewardParameterC::class, 'updateRewardPoints'])->name('updateRewardPoints');
// Delete Reward Points
Route::post('deleterewardpoints', [RewardParameterC::class, 'deleteRewardPoints'])->name('deleterewardpoints');
// Get Reward Points CSV
Route::get('rewardpointsexport', [RewardParameterC::class, 'rewardPointsExport'])->name('rewardpointsexport');


///////////////////////  Night Aduit log ///////////////////////
Route::get('nightauditlog', [NightAuditlogController::class, 'nightAuditLog'])->name('nightauditlog');
Route::post('fetchNightAuditLog', [NightAuditlogController::class, 'fetchNightAuditLog'])->name('fetchNightAuditLog');


// Api 
Route::post('fetchpayApiData', [CheckRegister::class, 'fetchpayApiData'])->name('fetchpayApiData');

// Cheque Cleared Register
Route::get('chequeclearedregister', [CheckRegister::class, 'chequeClearedRegister'])->name('chequeclearedregister')->middleware('company');
Route::post('fetchchequecleareddata', [CheckRegister::class, 'fetchChequeClearedData'])->name('fetchchequecleareddata');

// Cheque Not Cleared Register
Route::get('chequenotclearedregister', [CheckRegister::class, 'chequeNotClearedRegister'])
    ->name('chequenotclearedregister')
    ->middleware('company');

Route::post('fetchchequenotcleareddata', [CheckRegister::class, 'fetchChequeNotClearedData'])
    ->name('fetchchequenotcleareddata');


// Route::post('fetchpayApiData', function () {
//     return response()->json(['status' => 'success', 'message' => 'Data fetched successfully']);
// })->name('fetchpayApiData');




//Open chremovelog
Route::get('chremovelog', [NightAuditlogController::class, 'chremovelog'])->name('chremovelog');
//Fetch chremovelog
Route::post('fetchchremovelog', [NightAuditlogController::class, 'fetchchremovelog'])->name('fetchchremovelog');
// Holiday Master Routes
Route::get('/holidaymaster', [HolidayController::class, 'index'])->name('holiday.index');
Route::get('/printholidaymaster', [HolidayController::class, 'printHolidayMaster'])->name('printholidaymaster');
Route::get('/holidaymaster/export', [HolidayController::class, 'exportHolidayMaster'])->name('holidaymaster.export');
Route::post('/holiday/store', [HolidayController::class, 'store'])->name('holiday.store');
Route::get('/holiday/data', [HolidayController::class, 'getData'])->name('holiday.data');
Route::delete('/holiday/{id}', [HolidayController::class, 'destroy'])->name('holiday.destroy');

// Inventory Dashboard
Route::get('invdashboard', [InventoryController::class, 'lookUpdashboard'])->name('invdashboard');

// Kitchen Closing Stock
Route::get('kitchenclosingstock', [InventoryController::class, 'kitchenclosingstock'])->name('kitchenclosingstock');
Route::post('kitchenclosingstocksubmit', [InventoryController::class, 'kitchenclosingstocksubmit'])->name('kitchenclosingstocksubmit');
Route::get('kitchenclosingstockdelete', [InventoryController::class, 'kitchenclosingstockdelete'])->name('kitchenclosingstockdelete');
// Kitchen Closing Stock - Edit
Route::get('updatekitchenclosingstock', [InventoryController::class, 'updatekitchenclosingstock'])->name('updatekitchenclosingstock');
Route::post('kitchenclosingstockupdate', [InventoryController::class, 'kitchenclosingstockupdate'])->name('kitchenclosingstockupdate');

Route::get('/total-purchase', [PurchaseController::class, 'totalPurchase'])
    ->name('total.purchase');

//code by abhishek
// Route::post('finalpurchaseregister', [PurchaseController::class, 'finalpurchaseregister'])->name('finalpurchaseregister');


Route::get('/housekeepingreport', [HouseKeeping::class, 'housekeepingreport']);

// taxreporpos
Route::get('taxreporpos', [ReportController::class, 'taxReporPos'])->name('taxreporpos');
Route::post('taxreporposdata', [ReportController::class, 'taxReporPosData'])->name('taxreporposdata');

// complimentaryreport
Route::get('complimentaryreport', [ReportController::class, 'complimentaryReport'])->name('complimentaryreport');
Route::post('complimentaryreportdata', [ReportController::class, 'complimentaryReportData'])->name('complimentaryreportdata');

// taxsummarypos
Route::get('taxsummarypos',      [ReportController::class, 'taxSummaryPos'])->name('taxsummarypos');
Route::post('taxsummaryposdata', [ReportController::class, 'taxSummaryPosData'])->name('taxsummaryposdata');

// Credit Report
Route::get('creditreport',      [ReportController::class, 'creditReport'])->name('creditreport');
Route::post('creditreportdata', [ReportController::class, 'creditReportData'])->name('creditreportdata');
// Credit Report — Print & Export
Route::get('creditreport/print',  [ReportController::class, 'printCreditReport'])->name('creditreport.print');
Route::get('creditreport/export', [ReportController::class, 'exportCreditReport'])->name('creditreport.export');


// React App Auto Login
Route::post('/react-login', [AutoLoginController::class, 'reactLogin'])->name('react.login')->middleware('throttle:10,1');

// ── Housekeeping QR Scan Login ────────────────────────────────────────────────
// QR code scan hone pe yahan aata hai: /hk-scan/{propertyid}/{roomno}
// Public route — no auth middleware (controller khud login handle karta hai)
Route::get('hk-scan/{propertyid}/{roomno}',  [HkQrLoginController::class, 'showLogin'])->name('hk.qr.login');
Route::post('hk-scan/{propertyid}/{roomno}', [HkQrLoginController::class, 'doLogin'])->name('hk.qr.doLogin');



Route::get('pendingmr', [InventoryController::class, 'pendingmr'])->name('pendingmr');
Route::post('finalpendingmr', [InventoryController::class, 'finalpendingmr'])->name('finalpendingmr');

// Feedback Form Route
Route::post('feedback/room-details', [FeedbackMasterController::class, 'getRoomDetails'])->name('feedback.roomDetails');
Route::get('feedback/{propertyid}', [FeedbackMasterController::class, 'feedback'])->name('feedback');
Route::post('feedback/submit', [FeedbackMasterController::class, 'store'])->name('feedback.store');
Route::post('feedbackqrgenerator', [FeedbackMasterController::class, 'feedbackQrGenerate'])->name('feedbackqrgenerator');

Route::get('/set-language', function () {
    $lang = request('lang', 'en');

    if (in_array($lang, ['en', 'es', 'fr', 'de', 'hi', 'ar'])) {
        Session::put('locale', $lang);
    }

    return redirect()->back();
})->name('lang.switch');

Route::get('feedbackreport', [FeedbackMasterController::class, 'feedbackreport'])->name('feedbackreport');
Route::post('feedbackreportdata', [FeedbackMasterController::class, 'feedbackreportdata'])->name('feedbackreportdata');

// ─── Service Request Register Report ─────────────────────────────────────────
Route::get('servicerequestregister',        [FeedbackMasterController::class, 'servicerequestregister'])->name('servicerequestregister');
Route::post('fetchservicerequestregister',  [FeedbackMasterController::class, 'fetchservicerequestregister'])->name('fetchservicerequestregister');

// ─── Daily Service Summary Report ────────────────────────────────────────────
Route::get('dailyservicesummary',          [FeedbackMasterController::class, 'dailyservicesummary'])->name('dailyservicesummary');
Route::post('fetchdailyservicesummary',    [FeedbackMasterController::class, 'fetchdailyservicesummary'])->name('fetchdailyservicesummary');

Route::get('/roomservice-portal/{propertyid}/{roomno}', [FeedbackMasterController::class, 'roomserviceqr'])->name('roomserviceqr');

Route::get('/guest-portal/{propertyid}/{roomno}', [FeedbackMasterController::class, 'index'])->name('guestportal');
Route::post('/guest-portal/{propertyid}/{roomno}/service-request', [FeedbackMasterController::class, 'serviceRequest'])
    ->name('guestportal.servicerequest');
Route::get('/guest-portal/{propertyid}/{roomno}/my-stay', [FeedbackMasterController::class, 'myStay'])->name('guestportal.mystay');
Route::get('/guest-portal/{propertyid}/{roomno}/hotel-info', [FeedbackMasterController::class, 'hotelInfo'])->name('guestportal.hotelinfo');
Route::get('/guest-portal/{propertyid}/{roomno}/my-profile', [FeedbackMasterController::class, 'myProfile'])->name('guestportal.myprofile');

Route::post('/guestportalqrgenerator', [FeedbackMasterController::class, 'guestPortalQrGenerate'])->name('guestportalqrgenerator');
Route::post('/service-request/view', [FeedbackMasterController::class, 'viewServiceRequest'])
    ->name('servicerequest.view');
Route::post('service-request/accept', [FeedbackMasterController::class, 'acceptServiceRequest'])->name('servicerequest.accept');
Route::post('service-request/reject', [FeedbackMasterController::class, 'rejectServiceRequest'])->name('servicerequest.reject');
Route::get('/guest-portal/{propertyid}/{roomno}/express-checkout', [FeedbackMasterController::class, 'expressCheckout'])
    ->name('guestportal.expresscheckout');

Route::post('/guest-portal/{propertyid}/{roomno}/express-checkout', [FeedbackMasterController::class, 'submitExpressCheckout'])
    ->name('guestportal.expresscheckoutsubmit');

// Livewire pilot — live booking search (temporary test page)
Route::get('/livewire-pilot', function () {
    return view('admin.livewire-pilot');
})->name('livewire.pilot');

Route::get('dashboard-modern', function() {
    // Redirect to existing loadProperty but with modern view
    return redirect()->route('company');
})->name('dashboard.modern');


// ═══ Denomination Module ═══
Route::get('/denomination', 'App\Http\Controllers\DenominationController@index')->name('denomination.index');
Route::get('/denomination/create', 'App\Http\Controllers\DenominationController@create')->name('denomination.create');
Route::post('/denomination/store', 'App\Http\Controllers\DenominationController@store')->name('denomination.store');
Route::get('/denomination/{sno}', 'App\Http\Controllers\DenominationController@show')->name('denomination.show');
Route::delete('/denomination/{sno}', 'App\Http\Controllers\DenominationController@destroy')->name('denomination.destroy');
Route::get('/denomination/formats', 'App\Http\Controllers\DenominationController@getFormats')->name('denomination.formats');
Route::post('/denomination/format/save', 'App\Http\Controllers\DenominationController@saveFormat')->name('denomination.format.save');
Route::get('/denomination/print/{sno}', 'App\Http\Controllers\DenominationController@print')->name('denomination.print');
