<?php

namespace Accolon\Redis;

class Redis
{
    private static \Redis $instance;

    public static function connect(
        string $host = "localhost",
        int $port = 6379,
        ?string $password = null,
        float $timeout = 1,
        int $delay = 100
    ) {
        static::$instance = new \Redis();
        static::$instance->connect($host, $port, $timeout, null, $delay, 0);
        if ($password) {
            static::$instance->auth($password);
        }
        static::config();
    }

    public static function config(int $db = 0)
    {
        static::$instance->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_JSON);
        static::$instance->setOption(\Redis::OPT_SCAN, \Redis::SCAN_RETRY);
        static::setDB($db);
    }

    public static function setDB(int $db)
    {
        static::$instance->select($db);
    }

    public static function get($keys)
    {
        return !is_array($keys) ? static::$instance->get($keys) : static::$instance->mGet($keys);
    }

    public static function set(string $key, string $value)
    {
        return static::$instance->set($key, $value);
    }

    public static function has(string $key)
    {
        return (bool) static::$instance->exists($key);
    }

    public static function del($keys)
    {
        return static::$instance->unlink($keys);
    }

    public static function rename(string $key, string $name)
    {
        static::$instance->rename($key, $name);
    }

    public static function allKeys()
    {
        return static::$instance->keys("*");
    }

    public static function getKeys(string $pattern)
    {
        return static::$instance->keys($pattern);
    }

    public static function forEach(string $pattern, callable $callback)
    {
        foreach (static::getKeys($pattern) as $key) {
            $callback(static::get($key));
        }
    }

    public static function clear()
    {
        static::$instance->flushAll();
    }

    public static function save()
    {
        return static::$instance->bgSave();
    }
}
