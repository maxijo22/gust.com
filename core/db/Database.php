<?php

namespace Myframework\db;

use PDO;
use PDOException;

class Database
{
    public function __construct(public readonly string $DRIVER = 'mysql')
    {
    }
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

        $stm = $this->con()->prepare($sql);
        if (!$stm->execute($data)) {
            throw new \Exception('unkown error has occured');
        };

        $result = $stm->fetchAll(PDO::FETCH_OBJ);
        return $result;
    }

    // public function

}
