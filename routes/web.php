<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/create-storage-link', function () {
    Artisan::call('storage:link');
    return 'Storage link created!';
});


Route::get('/', function () {
    return view('welcome');
});
