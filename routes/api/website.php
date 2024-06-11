<?php

use App\Mail\DueTaskToClient;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::namespace('Api\Website')->prefix('website')->middleware('setLocale')->group(function () {
    Route::post('login', "Auth\AuthController@login");
    Route::post('send-code', "Auth\AuthController@sendCode");
    Route::post('forget-password', "Auth\AuthController@forgetPassword");

    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('logout', 'Auth\AuthController@logout');

        Route::namespace('Profile')->group(function () {
            Route::get('profile', 'ProfileController@profile');
            Route::put('profile', 'ProfileController@update');
            Route::patch('profile/update-password', 'ProfileController@updatePassword');
        });
//        helpers
        Route::namespace('Flow')->group(function () {
            Route::get('flow', 'FlowController@flows');
            Route::get('flow/{id}', 'FlowController@show');
            Route::post('flow/evalution_form/store', 'FlowEvalutionFormController@store');
        });

        Route::namespace('Activity')->group(function () {
            Route::get('activity', 'ActivityController@activities');
            Route::get('activity/statistics', 'ActivityController@statistics');
            Route::get('activity/{activity}', 'ActivityController@activity');
            Route::patch('activity/{activity}', 'ActivityController@markAsCompleted');
            Route::post('activity/{activity}', 'ActivityController@activityRemark');
            Route::post('answer/{activity}/multi_choice', 'ActivityController@answerMultiChoiceAssessment');
            Route::post('answer/{activity}/fill_blank', 'ActivityController@fillBlankAnswer');
            Route::post('answer/{activity}/drag_drop', 'ActivityController@dragDropQuestion');
            Route::post('showCertificate/flow/{flow}', 'ActivityController@showCertificate');
            Route::get('showAllCertificates', 'ActivityController@showAllCertificates');
        });
        Route::namespace('Setting')->group(function () {
            Route::get('contact', "SettingController@contact");
            Route::get('how-it-work', "SettingController@howItWork");
        });

        Route::namespace('Statictics')->group(function () {
            Route::get('home', "StatisticController@home");
            Route::get('header-statistics', "StatisticController@headerStatistics");
        });


        Route::namespace('Helper')->group(function () {
            Route::get('country', 'HelperController@country');
            Route::get('job-title', 'HelperController@jobTitle');
            Route::get('department', 'HelperController@department');
            Route::get('group', 'HelperController@group');
            Route::get('form', 'HelperController@evalution_form');
        });
    });


});

    Route::get('test_mail', function () {

        // try {

            $data = [
                'name' => 'areej',
                'email' => 'areejibrahim222@gmail.com',
                "admin_position" => "admin_position",
                'hr_mail' => setting('hr_mail') ?? "'areejibrahim222@gmail.com'",
                "department" => "department",
                "position" => "position",
            ];
            Mail::to($data['email'])->send(new \App\Mail\ProcessCompletedToClient($data));

            return 'ok';

        // } catch(\Exception $e) {
        //     dd($e->getMesssage());
        // }


    });
