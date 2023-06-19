<?php

namespace App\Models;

use Myframework\db\Model;
use Myframework\db\QueryBuilder as DB;

class TextModel extends Model
{
    private string $cart_sid;

    public function removeExpiredCart()
    {
        $current_time = time();
        DB::delete('cart')->where("expire_on  < $current_time")->execute();
    }

  
}
