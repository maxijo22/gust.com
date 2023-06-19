<?php

define("DS", DIRECTORY_SEPARATOR);

class MyCache
{

    private $filePath = 'tmp' . DS;
    public function setItem(string $key, mixed $data, string $exp): bool
    {
        $cacheData = [
            'data' => $data,
            'expiration' => time() + $exp
        ];

        return file_put_contents($this->filePath . $key, serialize($cacheData));

    }

    public function getItem(string $key): mixed
    {
        $cache = $this->filePath . $key;

        if (!file_exists($cache)) {
            die("item does not exist");
        }

        $cacheData = unserialize(file_get_contents($cache));

        if ($cacheData['expiration'] < time()) {
            die("expired cacheData");
        }


        return $cacheData['data'];
    }


    public function hasItem(): bool
    {
        return true;
    }
    private function removeItem(string $cacheItemPath)
    {
        return unlink($cacheItemPath);
    }
}



$cache = new MyCache();
$data = ['name' => 'john doe'];


$cacheData = $cache->getItem('my.cache');

var_dump($cacheData);


// var_dump($cache->setItem('my.cache', $data, time() * 6));