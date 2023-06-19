<?php

use Myframework\Http\Response;
use Myframework\Router\Route;


Route::get('/text', function (Response $response) {
    $response->withJson(['key' => 'value']);
});


Route::get('/', 'home@index')->middleware(['guest']);