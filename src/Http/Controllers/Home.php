<?php

namespace App\Http\Controllers;

use Myframework\Http\Response;

class Home
{

  public function index(Response $response)
  {
    view('home', ['page' => 'Homepage']);
  }
}