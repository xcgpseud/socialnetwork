<?php

use Illuminate\Support\Facades\Auth;

Auth::routes();

Route::get('{any}', 'AppController@index')
    ->where('any', '.*')
    ->middleware('auth')
    ->name('home');
