<?php

namespace Myframework\db;

use PDO;

class QueryBuilder
{
    private $transaction;
    private $query;
    private $bindings;
    private string $table;
    private array $data;

    public function __construct()
    {
        $this->transaction = new Transaction();
        $this->query = '';
        $this->bindings = [];
    }


    public function getQueryString()
    {

        dump(['sql' => $this->query, 'bindings' => $this->bindings]);
        return $this;
    }

    public static function table(string $table)
    {
        $self = new self();
        $self->table = $table;
        return $self;
    }

    public static function select($columns = '*')
    {
        $self = new self();
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        $self->query = 'SELECT ' . implode(', ', $columns);
        return $self;
    }



    public function from($table)
    {
        $this->query .= ' FROM ' . $table;
        return $this;
    }

    public function where($condition)
    {
        if (is_string($condition)) {
            $this->query .= ' WHERE ' . $condition;
        }

        if (is_array($condition)) {
            $columns = [];
            $placeholders = [];
            $values = [];

            foreach ($condition as $column => $value) {
                $columns[] = $column;
                $placeholders[] = $this->addBinding($value);
                $values[] = $value;
            }


            $this->query .= ' WHERE ' . implode(', ', $columns) . '=' . implode(', ', $placeholders) . '';
            $this->data = $condition;
            $this->bindings = array_merge($this->bindings);
        }


        return $this;
    }

    public function and (array $condition)
    {
        $columns = [];
        $placeholders = [];
        $values = [];

        foreach ($condition as $column => $value) {
            $columns[] = $column;
            $placeholders[] = $this->addBinding($value);
            $values[] = $value;
        }


        $this->query .= ' AND ' . implode(', ', $columns) . '=' . implode(', ', $placeholders) . '';
        $this->data = $condition;
        $this->bindings = array_merge($this->bindings);

        return $this;
    }



    public function whereIn($column, $values)
    {
        $placeholders = [];

        foreach ($values as $value) {
            $placeholders[] = $this->addBinding($value);
        }

        $this->query .= ' WHERE ' . $column . ' IN (' . implode(', ', $placeholders) . ')';
        return $this;
    }

    public function orderBy($column, $direction = 'ASC')
    {
        $this->query .= ' ORDER BY ' . $column . ' ' . $direction;
        return $this;
    }

    public function limit($limit)
    {
        $this->query .= ' LIMIT ' . $limit;
        return $this;
    }

    public function offset($offset)
    {
        $this->query .= ' OFFSET ' . $offset;
        return $this;
    }

    public function query($pattern = 'array')
    {

        if ($pattern == 'array') {
            $pattern = PDO::FETCH_ASSOC;
        } elseif ($pattern == 'column') {
            $pattern = PDO::FETCH_COLUMN;
        } else {
            $pattern = PDO::FETCH_OBJ;
        }

        return $this->transaction->query($this->query, $this->bindings, $pattern);

    }
    public function getSingle()
    {
        return $this->transaction->fetchObject($this->query, $this->bindings);
    }
    public function execute()
    {
        return $this->transaction->execute($this->query, $this->bindings);
    }


    public function insert($data)
    {

        $columns = [];
        $placeholders = [];
        $values = [];

        foreach ($data as $column => $value) {
            $columns[] = $column;
            $placeholders[] = $this->addBinding($value);
            $values[] = $value;
        }


        $this->query = 'INSERT INTO ' . $this->table . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
        $this->data = $data;
        $this->bindings = array_merge($this->bindings);

        // dump($this->bindings);
        // dump($this->query);
        // dump($this->data);
        // dd($data);

        return $this;
    }

    public function update($data)
    {
        $set = [];
        $values = [];

        foreach ($data as $column => $value) {
            $set[] = $column . '=' . $this->addBinding($value) . ' ';
            $values[] = $value;
        }

        $this->query = 'UPDATE ' . $this->table . ' SET ' . implode(', ', $set);
        $this->bindings = array_merge($this->bindings);

        return $this;
    }

    public static function delete(string $table)
    {

        $self = new self();
        $self->query = 'DELETE FROM ' . $table;
        return $self;
    }


    private function addBinding($value)
    {
        $placeholder = ':param' . count($this->bindings);
        $this->bindings[$placeholder] = $value;
        return $placeholder;
    }

    public function join($table, $firstColumn, $operator, $secondColumn)
    {
        $this->query .= ' JOIN ' . $table . ' ON ' . $firstColumn . ' ' . $operator . ' ' . $secondColumn;
        return $this;
    }

    public function leftJoin($table)
    {
        $this->query .= ' LEFT JOIN ' . $table;
        return $this;
    }

    public function rightJoin($table)
    {
        // $this->query .= ' RIGHT JOIN ' . $table . ' ON ' . $firstColumn . ' ' . $operator . ' ' . $secondColumn;
        $this->query .= ' RIGHT JOIN ' . $table;
        // $firstColumn, $operator, $secondColumn
        // ' ON ' . $firstColumn . ' ' . $operator . ' ' . $secondColumn
        return $this;
    }

    public function on($condition)
    {
        $this->query .= ' ON ' . $condition;
        return $this;
    }

    public function groupBy($column)
    {
        $this->query .= ' GROUP BY ' . $column;
        return $this;
    }

    public function having($condition)
    {
        $this->query .= ' HAVING ' . $condition;
        return $this;
    }

    public function count($column = '*')
    {
        $this->query = 'SELECT COUNT(' . $column . ') AS count';
        return $this;
    }
    public function innerJoin($table, $firstColumn, $operator, $secondColumn)
    {
        $this->query .= ' INNER JOIN ' . $table . ' ON ' . $firstColumn . ' ' . $operator . ' ' . $secondColumn;
        return $this;
    }
    public function orWhere($condition, $value = null)
    {
        if ($value === null) {
            $this->query .= ' OR ' . $condition;
        } else {
            $placeholder = $this->addBinding($value);
            $this->query .= ' OR ' . $condition . ' = ' . $placeholder;
        }

        return $this;
    }
    public function orWhereIn($column, $values)
    {
        $placeholders = [];

        foreach ($values as $value) {
            $placeholders[] = $this->addBinding($value);
        }

        $this->query .= ' OR ' . $column . ' IN (' . implode(', ', $placeholders) . ')';
        return $this;
    }
    public function orWhereRaw($condition)
    {
        $this->query .= ' OR ' . $condition;
        return $this;
    }

    public function notIn($column, $values)
    {
        $placeholders = [];

        foreach ($values as $value) {
            $placeholders[] = $this->addBinding($value);
        }

        $this->query .= ' WHERE ' . $column . ' NOT IN (' . implode(', ', $placeholders) . ')';
        return $this;
    }
    public function notBetween($column, $value1, $value2)
    {
        $placeholder1 = $this->addBinding($value1);
        $placeholder2 = $this->addBinding($value2);
        $this->query .= ' WHERE ' . $column . ' NOT BETWEEN ' . $placeholder1 . ' AND ' . $placeholder2;
        return $this;
    }

    public function notNull($column)
    {
        $this->query .= ' WHERE ' . $column . ' NOT NULL';
        return $this;
    }
    public function max($column)
    {
        $this->query = 'SELECT MAX(' . $column . ') AS max';
        return $this;
    }

    public function min($column)
    {
        $this->query = 'SELECT MIN(' . $column . ') AS min';
        return $this;
    }

    public function sum($column)
    {
        $this->query = 'SELECT SUM(' . $column . ') AS sum';
        return $this;
    }

    public function avg($column)
    {
        $this->query = 'SELECT AVG(' . $column . ') AS avg';
        return $this;
    }

    public function distinct()
    {
        $this->query = 'SELECT DISTINCT ' . substr($this->query, 7);
        return $this;
    }

    public function union($query)
    {
        $this->query .= ' UNION ' . $query;
        return $this;
    }

    public function unionAll($query)
    {
        $this->query .= ' UNION ALL ' . $query;
        return $this;
    }

    public function exists($query)
    {
        $this->query = 'SELECT EXISTS (' . $query . ')';
        return $this;
    }

    public function notExists($query)
    {
        $this->query = 'SELECT NOT EXISTS (' . $query . ')';
        return $this;
    }

    public function between($column, $value1, $value2)
    {
        $placeholder1 = $this->addBinding($value1);
        $placeholder2 = $this->addBinding($value2);
        $this->query .= ' WHERE ' . $column . ' BETWEEN ' . $placeholder1 . ' AND ' . $placeholder2;
        return $this;
    }

    public function like($column, $pattern)
    {
        $placeholder = $this->addBinding($pattern);
        $this->query .= ' WHERE ' . $column . ' LIKE ' . $placeholder;
        return $this;
    }

    public function notLike($column, $pattern)
    {
        $placeholder = $this->addBinding($pattern);
        $this->query .= ' WHERE ' . $column . ' NOT LIKE ' . $placeholder;
        return $this;
    }

    public function isNull($column)
    {
        $this->query .= ' WHERE ' . $column . ' IS NULL';
        return $this;
    }

    public function isNotNull($column)
    {
        $this->query .= ' WHERE ' . $column . ' IS NOT NULL';
        return $this;
    }
}