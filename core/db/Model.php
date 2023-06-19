<?php

namespace Myframework\db;

use PDO;
use PDOException;

class Model
{
    // public array $errors;
    public array $errors;
    public function con()
    {

        try {
            $dsn = 'mysql:hostname=localhost;dbname=easybay';
            return new PDO($dsn, 'root', '');
        } catch (PDOException $e) {
            dd($e->getMessage());
        };
    }

    public function query(string $sql, array $data = [])
    {
        // if($this-)

        $stm = $this->con()->prepare($sql);
        // dd($data);
        if (!$stm->execute($data)) {
            throw new \Exception('unkown error has occured');
        };

        $result = $stm->fetchAll(PDO::FETCH_OBJ);
        return $result;
    } 

    // public function __destruct()
    // {
    //   $this->con();      
    // }

}
