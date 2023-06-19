<?php
use Myframework\Http\Response;

// use function DI\create;


return [
    Response::class => function () {
        return new Response();
    },
];