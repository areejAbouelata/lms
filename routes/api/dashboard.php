<?php

use Illuminate\Support\Facades\Route;

Route::namespace('Api\Dashboard')->prefix('dashboard')->middleware('setLocale')->group(function () {
    Route::post('login', "Auth\AuthController@login");

    Route::post('send-code', "Auth\AuthController@sendCode");
    Route::post('forget-password', "Auth\AuthController@forgetPassword");
    Route::namespace('Setting')->group(function () {
        Route::apiResource('setting', 'SettingController');
    });

    Route::group(['middleware' => 'auth:api', 'admin'], function () {
        Route::post('logout', 'Auth\AuthController@logout');

        Route::namespace('Profile')->group(function () {
            Route::get('profile', 'ProfileController@profile');
            Route::put('profile', 'ProfileController@update');
            Route::patch('profile/update-password', 'ProfileController@updatePassword');
        });

        Route::namespace('Admin')->group(function () {
            Route::apiResource('admins', 'AdminController');
            Route::get('admins-without-pagination', 'AdminController@indexWithoutPagination');
            Route::patch('admins/{admin}/toggle-active', 'AdminController@toggleActive');
        });

        Route::namespace('Client')->group(function () {
            Route::patch('clients-toggle-active', 'ClientController@toggleActive');
            Route::patch('clients-assign-to-group', 'ClientController@assignToGroup');
            Route::patch('clients-assign-to-flow', 'ClientController@assignToFlow');
            Route::patch('clients-un-assign-to-flow', 'ClientController@unAssignToFlow');
            Route::apiResource('clients', 'ClientController');
            Route::get('clients-without-pagination', 'ClientController@indexWithoutPagination');
            Route::get('clients-activities/client/{user}/flow/{flow}', 'ClientController@clientActivitiesByFlow');
        });

        Route::namespace('Group')->group(function () {
            Route::apiResource('groups', 'GroupController');
            Route::patch('groups/{group}/toggle-active', 'GroupController@toggleActive');
        });
        Route::namespace('Slider')->group(function () {
            Route::apiResource('slider', 'SliderController');
        });

        Route::namespace('Department')->group(function () {
            Route::apiResource('department', 'DepartmentController');
            Route::get('department-without-pagination', 'DepartmentController@indexWithoutPagination');
            Route::patch('department-toggle-active', 'DepartmentController@toggleActive');

        });

        Route::namespace('Flow')->group(function () {
            Route::apiResource('flow', 'FlowController');
            Route::get('flow-without-pagination', 'FlowController@indexWithoutPagination');
            Route::get('flow-users/{flow}', 'FlowController@users');
            Route::get('flow-copy/{flow}', 'FlowController@copyFlow');
        });

        Route::namespace('Activity')->group(function () {
            Route::apiResource('activity', 'ActivityController');
            Route::get('activity-without-pagination', 'ActivityController@indexWithoutPagination');
            Route::patch('activity/{activity}/toggle-active', 'ActivityController@toggleActive');
            Route::get('activity-flow/{flow}', 'ActivityController@flowActivities');

            Route::post('activity/updateQuestion/{question}', 'ActivityController@updateQuestion');
            Route::post('activity/addAnswer/{question}', 'ActivityController@addAnswer');

            Route::post('activity/deleteAnswer/{assessment}', 'ActivityController@deleteAnswer');
        });
        Route::namespace('Assessment')->group(function () {
            Route::apiResource('assessment', 'AssessmentController');
        });
        Route::namespace('Country')->group(function () {
            Route::apiResource('country', 'CountryController');
            Route::get('country-without-pagination', 'CountryController@indexWithoutPagination');
            Route::patch('country-toggle-active', 'CountryController@toggleActive');
        });
        
        Route::namespace('EvalutionForm')->group(function () {
            Route::apiResource('form', 'EvalutionFormController');
            Route::get('form-without-pagination', 'EvalutionFormController@indexWithoutPagination');
            Route::patch('form-toggle-active', 'EvalutionFormController@toggleActive');
        });
        
        
        Route::namespace('JobTitle')->group(function () {
            Route::apiResource('job_title', 'JobTitleController');
            Route::get('job-title-without-pagination', 'JobTitleController@indexWithoutPagination');

        });
        Route::namespace('Quote')->group(function () {
            Route::apiResource('quote', 'QuoteController');
        });
        Route::namespace('Permission')->group(function () {
            Route::apiResource('permission', 'PermissionController');
            Route::get('permission-without-pagination', 'PermissionController@indexWithoutPagination');

        });
        Route::namespace('Role')->group(function () {
            Route::apiResource('role', 'RoleController');
            Route::get('role-without-pagination', 'RoleController@indexWithoutPagination');
        });
//        Route::namespace('Setting')->group(function () {
//            Route::apiResource('setting', 'SettingController');
//        });
        Route::namespace('Statictics')->group(function () {
            Route::get('header-statistics', 'StatisticController@headerStatistics');
            Route::get('home', 'StatisticController@home');
        });

        Route::namespace('Nationality')->group(function () {
            Route::get('nationalities', 'NationalitiesController@index');
            Route::get('nationalities-with-paginte', 'NationalitiesController@indexWithPaginte');
            Route::get('codes', 'NationalitiesController@codes');
        });
    });
    Route::get('clients-export-sample', 'Client\ClientByExcelController@export');
    Route::post('clients-import-sample', 'Client\ClientByExcelController@import');
    Route::get('flow-export', 'Flow\ExportFlowController@export');
    Route::get('activity-export/{flow}', 'Activity\ActivityExportController@export');
    Route::get('clients-export-flow', 'Client\ClientFlowExcelController@export');
    Route::get('clients-export-by-flow/{flow}', 'Flow\ExportFlowController@flowUsers');
    Route::get('department-export', 'Department\DepartmentController@export');
    Route::get('country-export', 'Country\CountryController@export');
    Route::get('group-export', 'Group\GroupController@export');
    Route::get('nationality-export', 'Nationality\NationalitiesController@export');
});
