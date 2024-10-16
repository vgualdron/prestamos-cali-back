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

Route::group(["prefix" => "/auth"], function () {
    Route::get('/get-active-token', [AuthController::class, 'getActiveToken'])->name('auth.getActiveToken');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::middleware(['middleware' => 'auth:api'])->post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/session"], function () {
    Route::get('/status', function (Request $request) {
        return 'OK';
    });
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/zone"], function () {
    Route::get('/list', [ZoneController::class, 'list'])->middleware('can:zone.list')->name('zone.list');
    Route::post('/create', [ZoneController::class, 'create'])->middleware('can:zone.create')->name('zone.create');
    Route::put('/update/{id}', [ZoneController::class, 'update'])->middleware('can:zone.update')->name('zone.update');
    Route::delete('/delete/{id}', [ZoneController::class, 'delete'])->middleware('can:zone.delete')->name('zone.delete');
    Route::get('/get/{id}', [ZoneController::class, 'get'])->middleware('can:zone.get')->name('zone.get');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/role"], function () {
    Route::get('/list', [RoleController::class, 'list'])->middleware('can:role.list')->name('role.list');
    Route::post('/create', [RoleController::class, 'create'])->middleware('can:role.create')->name('role.create');
    Route::put('/update/{id}', [RoleController::class, 'update'])->middleware('can:role.update')->name('role.update');
    Route::delete('/delete/{id}', [RoleController::class, 'delete'])->middleware('can:role.delete')->name('role.delete');
    Route::get('/get/{id}', [RoleController::class, 'get'])->middleware('can:role.get')->name('role.get');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/permission"], function () {
    Route::get('/list', [PermissionController::class, 'list'])->name('permission.list');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/yard"], function () {
    Route::get('/list/{yard}/{displayAll}', [YardController::class, 'list'])->middleware('can:yard.list')->name('yard.list');
    Route::get('/list-by-zone/{zone}/{displayAll}', [YardController::class, 'listByZone'])->middleware('can:yard.list')->name('yard.list');
    Route::post('/create', [YardController::class, 'create'])->middleware('can:yard.create')->name('yard.create');
    Route::put('/update/{id}', [YardController::class, 'update'])->middleware('can:yard.update')->name('yard.update');
    Route::delete('/delete/{id}', [YardController::class, 'delete'])->middleware('can:yard.delete')->name('yard.delete');
    Route::get('/get/{id}', [YardController::class, 'get'])->middleware('can:yard.get')->name('yard.get');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/user"], function () {
    Route::get('/list/{displayAll}', [UserController::class, 'list'])->middleware('can:user.list')->name('user.list');
    Route::get('/list-by-role-name/{displayAll}/{name}/{city}', [UserController::class, 'listByRoleName'])->name('user.listByRoleName');
    Route::post('/create', [UserController::class, 'create'])->middleware('can:user.create')->name('user.create');
    Route::put('/update/{id}', [UserController::class, 'update'])->middleware('can:user.update')->name('user.update');
    Route::delete('/delete/{id}', [UserController::class, 'delete'])->middleware('can:user.delete')->name('user.delete');
    Route::get('/get/{id}', [UserController::class, 'get'])->name('user.get');
    Route::put('/updateProfile/{id}', [UserController::class, 'updateProfile'])->middleware('can:user.updateProfile')->name('user.updateProfile');
    Route::put('/update-push-token', [UserController::class, 'updatePushToken'])->name('user.updatePushToken');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/new"], function () {
    Route::get('/list/{status}', [NovelController::class, 'list'])->middleware('can:new.list')->name('new.list');
    Route::post('/create', [NovelController::class, 'create'])->middleware('can:new.create')->name('new.create');
    Route::put('/update/{id}', [NovelController::class, 'update'])->name('new.update');
    Route::put('/update-status/{id}', [NovelController::class, 'updateStatus'])->name('new.changeStatus');
    Route::put('/complete-data/{id}', [NovelController::class, 'completeData'])->name('review.completeData');
    Route::delete('/delete/{id}', [NovelController::class, 'delete'])->middleware('can:new.delete')->name('new.delete');
    Route::get('/get/{id}', [NovelController::class, 'get'])->name('new.get');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/diary"], function () {
    Route::get('/list/{date}/{user}/{moment}', [DiaryController::class, 'list'])->name('diary.list');
    Route::get('/list-day-by-day/{date}/{user}/{moment}', [DiaryController::class, 'listDayByDay'])->name('diary.list');
    Route::get('/list-visits-review/{date}', [DiaryController::class, 'listVisitsReview'])->name('diary.listVisitsReview');
    Route::get('/get-status-cases/{idNew}', [DiaryController::class, 'getStatusCases'])->name('diary.getStatusCases');
    Route::post('/approve-visit', [DiaryController::class, 'approveVisit'])->name('diary.approveVisit');
    Route::post('/create', [DiaryController::class, 'create'])->name('diary.create');
    Route::put('/update/{id}', [DiaryController::class, 'update'])->name('diary.update');
    Route::put('/update-status/{id}', [DiaryController::class, 'updateStatus'])->name('diary.changeStatus');
    Route::delete('/delete/{id}', [DiaryController::class, 'delete'])->name('diary.delete');
    Route::get('/get/{id}', [DiaryController::class, 'get'])->name('diary.get');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/file"], function () {
    Route::post('/create', [FileController::class, 'create'])->name('file.create');
    Route::delete('/delete/{id}', [FileController::class, 'delete'])->name('file.delete');
    Route::post('/get', [FileController::class, 'get'])->name('file.get');
    Route::put('/update/{id}', [FileController::class, 'update'])->name('new.update');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/zip"], function () {
    Route::get('/list', [ZipController::class, 'list'])->name('zip.delete');
    Route::get('/create', [ZipController::class, 'create'])->name('zip.create');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/configuration"], function () {
    Route::get('/', [ConfigurationController::class, 'index'])->middleware('can:parameter.list')->name('parameter.list');
    Route::get('/{id}', [ConfigurationController::class, 'show'])->middleware('can:parameter.list')->name('parameter.get');
    Route::post('/', [ConfigurationController::class, 'store'])->middleware('can:parameter.list')->name('parameter.create');
    Route::put('/{id}', [ConfigurationController::class, 'update'])->middleware('can:parameter.list')->name('parameter.update');
    Route::delete('/{id}', [ConfigurationController::class, 'destroy'])->middleware('can:parameter.list')->name('parameter.delete');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/listing"], function () {
    Route::get('/', [ListingController::class, 'index']);
    Route::get('/mine', [ListingController::class, 'getMine']);
    Route::get('/{id}', [ListingController::class, 'show']);
    Route::post('/', [ListingController::class, 'store']);
    Route::put('/{id}', [ListingController::class, 'update']);
    Route::delete('/{id}', [ListingController::class, 'destroy']);
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/lending"], function () {
    Route::get('/list/{idListing}', [LendingController::class, 'index']);
    Route::get('/list/{idListing}/payments/current-date', [LendingController::class, 'getLendingsWithPaymentsCurrentDate']);
    Route::get('/list/{idListing}/current-date', [LendingController::class, 'getLendingsFromListCurrentDate']);
    Route::get('/{id}', [LendingController::class, 'show']);
    Route::post('/', [LendingController::class, 'store']);
    Route::put('/{id}', [LendingController::class, 'update']);
    Route::put('/update-rows/all', [LendingController::class, 'updateOrderRows']);
    Route::delete('/{id}', [LendingController::class, 'destroy']);
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/payment"], function () {
    Route::get('list/{status}', [PaymentController::class, 'index']);
    Route::get('/lending/{idLending}', [PaymentController::class, 'getPaymentsForLending']);
    Route::get('/list/{idListing}/current-date', [PaymentController::class, 'getPaymentsFromListCurrentDate']);
    Route::get('/reference/{reference}', [PaymentController::class, 'getPaymentByReference']);
    Route::get('/{id}', [PaymentController::class, 'show']);
    Route::post('/', [PaymentController::class, 'store']);
    Route::put('/{id}', [PaymentController::class, 'update']);
    Route::delete('/{id}', [PaymentController::class, 'destroy']);
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/district"], function () {
    Route::get('/', [DistrictController::class, 'list'])->name('district.list');
    Route::post('/', [DistrictController::class, 'create'])->name('district.create');
    Route::put('/{id}', [DistrictController::class, 'update'])->name('district.update');
    Route::delete('/{id}', [DistrictController::class, 'delete'])->name('district.delete');
    Route::get('/{id}', [DistrictController::class, 'get'])->name('district.get');
});

Route::group(['middleware' => 'auth:api' , "prefix" => "/report"], function () {
    Route::get('/', [ReportController::class, 'list'])->name('report.list');
    Route::get('/{id}', [ReportController::class, 'execute'])->name('report.execute');
});