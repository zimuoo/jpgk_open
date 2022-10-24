<?php

declare (strict_types = 1);
namespace Zimuoo\Jpgkopen\Http;

class Redis
{
    public function __construct($option=[])
    {
    }
    public static function connect($option=[])
    {
        $connection = new \Redis();
        $redisConfig= $option;
        $ret = $connection->connect($option['host'], $option['port']);
        if ($ret === false) {
            throw new \RuntimeException(sprintf('Failed to connect Redis server: [%s] %s', $connection->errCode, $connection->errMsg));
        }
        if (isset($option['password'])) {
            $option['password'] = (string)$option['password'];
            if ($option['password'] !== '') {
                $connection->auth($option['password']);
            }
        }
        if (isset($option['database'])) {
            $connection->select($option['database']);
        }
        return $connection;
    }
}