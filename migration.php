<?php
declare(strict_types=1);
use Myframework\Http\App;

define('DS', DIRECTORY_SEPARATOR);

require __DIR__ . DS . "vendor" . DS . "autoload.php";
$app = new App(__DIR__);
$app->migration()->apply();



