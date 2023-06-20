<?php

use Myframework\Http\Response;
use Myframework\Router\Route;


Route::group('dashboard', function () {
    Route::get('/', 'home@index')->middleware(['guest']);

    Route::get('hello', function () {
        echo "done and dusted";
    });
});