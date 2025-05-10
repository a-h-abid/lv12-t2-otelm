<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'Welcome to debug route.';
});

Route::get('phpinfo', function () {
    phpinfo();
});
