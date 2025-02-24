<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/sidebarpage', function () {
    return view('layouts.sidebar');
});

Route::get('/leaderboardAM', function () {
    return view('leaderboardAM');
});
