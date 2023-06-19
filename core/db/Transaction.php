<?php

namespace Myframework\db;
use PDO;

class Transaction extends Database
{
    public function beginTransaction()
    {
        $this->con()->beginTransaction();
    }

    public function commit()
    {
        $this->con()->commit();
    }

    public function rollback()
    {
        $this->con()->rollBack();
    }

    public function lastInsertedId()
    {
        return $this->con()->lastInsertId();
    }

    // Execute a query and return the result set
    public function query($query, $params = [] , $pattern = PDO::FETCH_OBJ)
    {
        $stmt = $this->con()->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll($pattern);
    }

    // Execute a query that doesn't return a result set
    public function execute($query, $params = [])
    {
        $stmt = $this->con()->prepare($query);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    // Execute a query and return a single value
    public function singleValue($query, $params = [])
    {
        $stmt = $this->con()->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    // Execute a query and return a single row as an object
    public function fetchObject($query, $params = [], $className = "stdClass")
    {
        $stmt = $this->con()->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchObject($className);
    }

    // Execute a query and return all rows as an associative array
    public function fetchAll($query, $params = [])
    {
        $stmt = $this->con()->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Execute multiple queries as a batch
    public function executeMultiple($queries)
    {
        foreach ($queries as $query) {
            $stmt = $this->con()->prepare($query);
            $stmt->execute();
        }
    }
}
