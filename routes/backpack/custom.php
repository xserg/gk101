<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes

    //Route::get('dashboard', 'AdminController@dashboard')->name('backpack.dashboard');
    //Route::get('/', 'AdminController@redirect')->name('backpack');

    Route::crud('department', 'DepartmentCrudController');
    Route::crud('division', 'DivisionCrudController');
    Route::crud('institution', 'InstitutionCrudController');
    Route::crud('staff', 'StaffCrudController');
    //Route::crud('user', 'UserCrudController');

    Route::get('upload', 'UploadController@uploadForm');
    Route::post('upload', 'UploadController@saveUpload');
    Route::crud('division-result', 'DivisionResultCrudController');
    Route::crud('field', 'FieldCrudController');
    Route::crud('file', 'FileCrudController');
    Route::crud('pump', 'PumpCrudController');
    Route::crud('pump-subcat', 'PumpSubcatCrudController');
    Route::crud('pump-categories', 'PumpCategoriesCrudController');
    Route::crud('registry', 'RegistryCrudController');
    Route::crud('reglog', 'ReglogCrudController');
    Route::crud('watched', 'WatchedCrudController');
}); // this should be the absolute last line of this file