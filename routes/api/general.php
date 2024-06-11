<?php

use Illuminate\Support\Facades\Route;

Route::prefix('general')->middleware('setLocale')->group(function () {
    Route::namespace('Api\General')->group(function () {
        Route::post('attachments', 'GeneralController@attachment');
    });
    Route::get('test' , function () {
        dd(  \Carbon\Carbon::parse('2023-09-06 15:10:09')->addDays(2)) ;
    }) ;
});
