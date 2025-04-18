<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
                        AuthController,
                        ZoneController,
                        RoleController,
                        PermissionController,
                        YardController,
                        UserController,
                        NovelController,
                        DiaryController,
                        FileController,
                        ZipController,
                        ConfigurationController,
                        ListingController,
                        LendingController,
                        PaymentController,
                        DistrictController,
                        ReportController,
                        ExpenseController,
                        AreaController,
                        ItemController,
                        QuestionController,
                        NequiController,
                        UploadDataController,
                        RedcollectorController,
                        ReddirectionController,
                        DeliveryController,
                        DiscountController,
                        WorkplanController,
                        TaskController,
                        LoanController,
                        DepositController,
                    };

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/health', function (Request $request) {
    return 'Health...';
});

Route::get('/download-image-from-url', [FileController::class, 'downloadImageFromUrl'])->name('file.downloadImageFromUrl');

Route::group(["prefix" => "/auth"], function () {
    Route::get('/get-active-token', [AuthController::class, 'getActiveToken'])->name('auth.getActiveToken');
    Route::middleware(['middleware' => 'validate.timestamp'])->post('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::middleware(['middleware' => 'auth:api'])->post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'] , "prefix" => "/session"], function () {
    Route::get('/status', function (Request $request) {
        return 'OK';
    });
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'] , "prefix" => "/zone"], function () {
    Route::get('/list', [ZoneController::class, 'list'])->name('zone.list');
    Route::post('/create', [ZoneController::class, 'create'])->middleware('can:zone.create')->name('zone.create');
    Route::put('/update/{id}', [ZoneController::class, 'update'])->middleware('can:zone.update')->name('zone.update');
    Route::delete('/delete/{id}', [ZoneController::class, 'delete'])->middleware('can:zone.delete')->name('zone.delete');
    Route::get('/get/{id}', [ZoneController::class, 'get'])->middleware('can:zone.get')->name('zone.get');
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/role"], function () {
    Route::get('/list', [RoleController::class, 'list'])->middleware('can:role.list')->name('role.list');
    Route::post('/create', [RoleController::class, 'create'])->middleware('can:role.create')->name('role.create');
    Route::put('/update/{id}', [RoleController::class, 'update'])->middleware('can:role.update')->name('role.update');
    Route::delete('/delete/{id}', [RoleController::class, 'delete'])->middleware('can:role.delete')->name('role.delete');
    Route::get('/get/{id}', [RoleController::class, 'get'])->middleware('can:role.get')->name('role.get');
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/permission"], function () {
    Route::get('/list', [PermissionController::class, 'list'])->name('permission.list');
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/yard"], function () {
    Route::get('/list/{yard}/{displayAll}', [YardController::class, 'list'])->name('yard.list');
    Route::get('/list-by-zone/{zone}/{displayAll}', [YardController::class, 'listByZone'])->name('yard.list');
    Route::post('/create', [YardController::class, 'create'])->middleware('can:yard.create')->name('yard.create');
    Route::put('/update/{id}', [YardController::class, 'update'])->middleware('can:yard.update')->name('yard.update');
    Route::delete('/delete/{id}', [YardController::class, 'delete'])->middleware('can:yard.delete')->name('yard.delete');
    Route::get('/get/{id}', [YardController::class, 'get'])->middleware('can:yard.get')->name('yard.get');
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/user"], function () {
    Route::get('/get-info', [UserController::class, 'getInfo'])->name('user.getInfo');
    Route::get('/list/{displayAll}', [UserController::class, 'list'])->name('user.list');
    Route::get('/list-by-role-name/{displayAll}/{name}/{city}', [UserController::class, 'listByRoleName'])->name('user.listByRoleName');
    Route::get('/list-by-area/{area}', [UserController::class, 'listByArea'])->name('user.listByArea');
    Route::post('/create', [UserController::class, 'create'])->middleware('can:user.create')->name('user.create');
    Route::put('/update/{id}', [UserController::class, 'update'])->middleware('can:user.update')->name('user.update');
    Route::delete('/delete/{id}', [UserController::class, 'delete'])->middleware('can:user.delete')->name('user.delete');
    Route::get('/get/{id}', [UserController::class, 'get'])->name('user.get');
    Route::put('/updateProfile/{id}', [UserController::class, 'updateProfile'])->name('user.updateProfile');
    Route::put('/update-push-token', [UserController::class, 'updatePushToken'])->name('user.updatePushToken');
    Route::put('/update-location', [UserController::class, 'updateLocation'])->name('user.updateLocation');
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/new"], function () {
    Route::get('/list/{status}', [NovelController::class, 'list'])->name('new.list');
    Route::get('/list-for-update/{status}/{query}', [NovelController::class, 'listForUpdate'])->name('new.listForUpdate');
    Route::get('/list-for-letter/{status}', [NovelController::class, 'listForLetter'])->name('new.listForLetter');
    Route::post('/create', [NovelController::class, 'create'])->name('new.create');
    Route::put('/update/{id}', [NovelController::class, 'update'])->name('new.update');
    Route::put('/update-status/{id}', [NovelController::class, 'updateStatus'])->name('new.changeStatus');
    Route::put('/complete-data/{id}', [NovelController::class, 'completeData'])->name('review.completeData');
    Route::delete('/delete/{id}', [NovelController::class, 'delete'])->middleware('can:new.delete')->name('new.delete');
    Route::get('/get/{id}', [NovelController::class, 'get'])->name('new.get');
    Route::get('/get-by-phone/{phone}', [NovelController::class, 'getByPhone'])->name('new.getByPhone');
    Route::get('/list-reds/{city}/{user}', [NovelController::class, 'listReds'])->name('new.listReds');
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/diary"], function () {
    Route::get('/list/{date}/{user}/{moment}', [DiaryController::class, 'list'])->name('diary.list');
    Route::get('/list-day-by-day/{date}/{user}/{moment}', [DiaryController::class, 'listDayByDay'])->name('diary.list');
    Route::get('/list-visits-review/{date}', [DiaryController::class, 'listVisitsReview'])->name('diary.listVisitsReview');
    Route::get('/get-status-cases/{idNew}', [DiaryController::class, 'getStatusCases'])->name('diary.getStatusCases');
    Route::get('/get-by-user-and-date/{user}/{date}', [DiaryController::class, 'getByUserAndDate'])->name('diary.getByUserAndDate');
    Route::post('/approve-visit', [DiaryController::class, 'approveVisit'])->name('diary.approveVisit');
    Route::post('/create', [DiaryController::class, 'create'])->name('diary.create');
    Route::put('/update/{id}', [DiaryController::class, 'update'])->name('diary.update');
    Route::put('/update-status/{id}', [DiaryController::class, 'updateStatus'])->name('diary.changeStatus');
    Route::delete('/delete/{id}', [DiaryController::class, 'delete'])->name('diary.delete');
    Route::get('/get/{id}', [DiaryController::class, 'get'])->name('diary.get');
    Route::put('/complete-data/{id}', [DiaryController::class, 'completeData'])->name('diary.completeData');
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/file"], function () {
    Route::post('/create', [FileController::class, 'create'])->name('file.create');
    Route::delete('/delete/{id}', [FileController::class, 'delete'])->name('file.delete');
    Route::post('/get', [FileController::class, 'get'])->name('file.get');
    Route::put('/update/{id}', [FileController::class, 'update'])->name('file.update');
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/zip"], function () {
    Route::get('/list', [ZipController::class, 'list'])->name('zip.delete');
    Route::get('/create', [ZipController::class, 'create'])->name('zip.create');
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/configuration"], function () {
    Route::get('/', [ConfigurationController::class, 'index'])->name('parameter.list');
    Route::get('/{id}', [ConfigurationController::class, 'show'])->middleware('can:parameter.list')->name('parameter.get');
    Route::post('/', [ConfigurationController::class, 'store'])->middleware('can:parameter.list')->name('parameter.create');
    Route::put('/{id}', [ConfigurationController::class, 'update'])->middleware('can:parameter.list')->name('parameter.update');
    Route::delete('/{id}', [ConfigurationController::class, 'destroy'])->middleware('can:parameter.list')->name('parameter.delete');
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/listing"], function () {
    Route::get('/', [ListingController::class, 'index']);
    Route::get('/mine', [ListingController::class, 'getMine']);
    Route::get('/{id}', [ListingController::class, 'show']);
    Route::get('get-info/{id}', [ListingController::class, 'getInfo']);
    Route::post('/', [ListingController::class, 'store']);
    Route::put('/{id}', [ListingController::class, 'update']);
    Route::delete('/{id}', [ListingController::class, 'destroy']);
    Route::get('/delivery/listing/{listing}/date/{date}', [ListingController::class, 'getDelivery']);
    Route::get('/list-with-deliveries/{date}', [ListingController::class, 'listWithDeliveries']);
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/lending"], function () {
    Route::get('/list/{idListing}', [LendingController::class, 'index']);
    Route::get('/list/{idListing}/payments/current-date', [LendingController::class, 'getLendingsWithPaymentsCurrentDate']);
    Route::get('/list/{idListing}/current-date', [LendingController::class, 'getLendingsFromListCurrentDate']);
    Route::get('/list-for-sale/{idListing}', [LendingController::class, 'getLendingsForSale']);
    Route::get('/{id}', [LendingController::class, 'show']);
    Route::post('/', [LendingController::class, 'store']);
    Route::put('/{id}', [LendingController::class, 'update']);
    Route::put('/update-rows/all', [LendingController::class, 'updateOrderRows']);
    Route::delete('/{id}', [LendingController::class, 'destroy']);
    Route::post('/renovate/{id}', [LendingController::class, 'renovate']);
    Route::post('/renew-old', [LendingController::class, 'renewOld']);
    Route::get('/history/{id}', [LendingController::class, 'history']);
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/payment"], function () {
    Route::get('list/{status}', [PaymentController::class, 'index']);
    Route::get('/lending/{idLending}', [PaymentController::class, 'getPaymentsForLending']);
    Route::get('/list/{idListing}/current-date', [PaymentController::class, 'getPaymentsFromListCurrentDate']);
    Route::get('/reference/{reference}', [PaymentController::class, 'getPaymentByReference']);
    Route::get('/{id}', [PaymentController::class, 'show']);
    Route::post('/', [PaymentController::class, 'store']);
    Route::put('/{id}', [PaymentController::class, 'update']);
    Route::delete('/{id}', [PaymentController::class, 'destroy']);
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/discount"], function () {
    Route::post('/', [DiscountController::class, 'store']);
    Route::delete('/{id}', [DiscountController::class, 'destroy']);
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/district"], function () {
    Route::get('/', [DistrictController::class, 'list'])->name('district.list');
    Route::post('/', [DistrictController::class, 'create'])->name('district.create');
    Route::put('/{id}', [DistrictController::class, 'update'])->name('district.update');
    Route::delete('/{id}', [DistrictController::class, 'delete'])->name('district.delete');
    Route::get('/{id}', [DistrictController::class, 'get'])->name('district.get');
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/report"], function () {
    Route::get('/', [ReportController::class, 'list'])->name('report.list');
    Route::get('/{id}', [ReportController::class, 'execute'])->name('report.execute');
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/expense"], function () {
    Route::get('list-by-user/{user}/{status}/exclude-items/{items}', [ExpenseController::class, 'listByUser'])->name('expense.listByUser');
    Route::get('/{status}/exclude-items/{items}', [ExpenseController::class, 'list'])->name('expense.list');
    Route::get('/item/{status}/{item}', [ExpenseController::class, 'listByItem'])->name('expense.list');
    Route::post('/', [ExpenseController::class, 'create'])->name('expense.create');
    Route::put('/{id}', [ExpenseController::class, 'update'])->name('expense.update');
    Route::delete('/{id}', [ExpenseController::class, 'delete'])->name('expense.delete');
    Route::get('/{id}', [ExpenseController::class, 'get'])->name('expense.get');
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/area"], function () {
    Route::get('/', [AreaController::class, 'index'])->name('area.list');
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/item"], function () {
    Route::get('area/{id}', [ItemController::class, 'index'])->name('item.list');
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/question"], function () {
    Route::get('/{status}/{type}', [QuestionController::class, 'list'])->name('question.list');
    Route::post('/', [QuestionController::class, 'create'])->name('question.create');
    Route::put('/{id}', [QuestionController::class, 'update'])->name('question.update');
    Route::delete('/{id}', [QuestionController::class, 'delete'])->name('question.delete');
    Route::get('/{id}', [QuestionController::class, 'get'])->name('question.get');
    Route::post('/get-status', [QuestionController::class, 'getStatus'])->name('question.getStatus');
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/nequi"], function () {
    Route::get('/listing/{id}', [NequiController::class, 'index'])->name('nequi.list');
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/redcollectors"], function () {
    Route::post('/', [RedcollectorController::class, 'create'])->name('redcollectors.create');
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/reddirections"], function () {
    Route::post('/', [ReddirectionController::class, 'create'])->name('reddirections.create');
    Route::get('/get-current-by-user/{user}', [ReddirectionController::class, 'getCurrentByUser'])->name('reddirections.getCurrentByUser');
    Route::get('/get-by-user-and-date/{user}/{date}', [ReddirectionController::class, 'getByUserAndDate'])->name('reddirections.getByUserAndDate');
    Route::get('/get-by-lending/{lending}', [ReddirectionController::class, 'getByLending'])->name('reddirections.getByLending');
    Route::put('/{id}', [ReddirectionController::class, 'update'])->name('reddirection.update');
    Route::delete('/{id}', [ReddirectionController::class, 'delete'])->name('reddirection.delete');
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/delivery"], function () {
    Route::post('/', [DeliveryController::class, 'create']);
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/workplan"], function () {
    Route::get('/{date}', [WorkplanController::class, 'list'])->name('workplan.list');
    Route::post('/', [WorkplanController::class, 'create'])->name('workplan.create');
    Route::put('/{id}', [WorkplanController::class, 'update'])->name('workplan.update');
    Route::delete('/{id}', [WorkplanController::class, 'delete'])->name('workplan.delete');
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/task"], function () {
    Route::get('/{status}', [TaskController::class, 'list'])->name('task.list');
    Route::post('/', [TaskController::class, 'create'])->name('task.create');
    Route::put('/{id}', [TaskController::class, 'update'])->name('task.update');
    Route::delete('/{id}', [TaskController::class, 'delete'])->name('task.delete');
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/loan"], function () {
    Route::get('/{status}', [LoanController::class, 'list'])->name('loan.list');
    Route::post('/', [LoanController::class, 'create'])->name('loan.create');
    Route::put('/{id}', [LoanController::class, 'update'])->name('loan.update');
    Route::delete('/{id}', [LoanController::class, 'delete'])->name('loan.delete');
    Route::get('/{id}', [LoanController::class, 'get'])->name('loan.get');
});

Route::group(['middleware' => ['auth:api', 'validate.timestamp'], "prefix" => "/deposit"], function () {
    Route::get('/{status}', [DepositController::class, 'list'])->name('deposit.list');
    Route::post('/', [DepositController::class, 'create'])->name('deposit.create');
    Route::put('/{id}', [DepositController::class, 'update'])->name('deposit.update');
    Route::delete('/{id}', [DepositController::class, 'delete'])->name('deposit.delete');
});

// endpoints de carga da datos
Route::post('/upload-data-payments', [UploadDataController::class, 'uploadPayments']);
Route::post('/create-new', [UploadDataController::class, 'createNew']);
Route::get('/list-news', [UploadDataController::class, 'listNews']);
