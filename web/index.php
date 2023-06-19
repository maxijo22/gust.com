<?php
declare(strict_types=1);
header('Access-Control-Allow-Origin:http://localhost:5173');
header('Access-Control-Allow-Credentials:true');
use Myframework\Http\Token;
use Myframework\Http\App;
// use Myframework\Http\Request;


define('BASE_PATH', dirname(__DIR__));
define("DS", DIRECTORY_SEPARATOR);
// define("_ASSET_" , "localhost" );
require BASE_PATH . "/vendor/autoload.php";


function isLogin()
{
    $request = Request::createFromGlobal();
    $token = $request->getToken();
    $decodeToken = Token::decode($token);
    if (!$decodeToken instanceof \stdClass) {
        return false;
    }
    return $decodeToken;
}


function getKey()
{
    $key = "310966ecf536dd13173b40d1edac0eb62a39fda768ebaf365cf127542ec9c4cc2c47084cedef0fe40dc390050124fbe74d9aa98550f86925cc41c5d151492637fa2e83bf34cdaccbb966fd4ace2528017e23206279d51ccc89fb6d4c2f1cdca588fcc3f7e8561d6a197860ddd386babecc501e4e5ab7c2c7ce953a198b1288be5db7cfd6c0f38070e74e819f649c2aaf37a0676e663f7cbe600775c981064377";
    return $key;
}

function csrf_token()
{
    $token = bin2hex(md5(uniqid(), true));
    $_SESSION['csrf_token'] = $token;
    return $token;
}


function get_token()
{
    if (empty($_SESSION['csrf_token'])) {
        return csrf_token();
    }
    return $_SESSION['csrf_token'];
}


function view(string $view_name, array $data)
{
    if (!empty($data) && is_array($data)) {
        extract($data);
    }

    $view_name = BASE_PATH . DS . "src" . DS . "Views" . DS . $view_name . ".view.php";

    if (file_exists($view_name)) {
        require $view_name;
        exit;
    } else {
        dd("View file $view_name not found");
    }

}

 

$app = new App(dirname(__DIR__));
$app->run();