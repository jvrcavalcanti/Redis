<?php

namespace Accolon\Redis;

class Redis
{
    private static \Redis $redis;
    private static array $channels = [];
    private static int $index = 0;

    public static function setConnection(
        string $password,
        string $host = "localhost",
        int $port = 6379,
        float $timeout = 1,
        int $delay = 100
    )
    {
        self::$redis = new \Redis();
        self::$redis->connect(
            $host,
            $port,
            $timeout,
            null,
            $delay,
            0
        );
        self::$redis->auth($password);
        self::config();
    }

    public static function config(int $db = 0)
    {
        self::$redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_JSON);
        self::$redis->setOption(\Redis::OPT_PREFIX, "accolon.");
        self::$redis->setOption(\Redis::OPT_SCAN, \Redis::SCAN_RETRY);
        self::$redis->select($db);
    }

    public static function get($keys)
    {

        return !is_array($keys) ? self::$redis->get($keys) : self::$redis->mGet($keys);
    }

    public static function set(string $key, string $value, int $time = 100)
    {
        return self::$redis->pSetEx($key, $time, $value);
    }

    public static function has(string $key)
    {
        return (bool) self::$redis->exists($key); 
    }

    public static function del($keys)
    {
        return self::$redis->del($keys);
    }

    public static function rename(string $key, string $name)
    {
        self::$redis->rename($key, $name);
    }

    public static function allKeys()
    {
        return self::$redis->keys("*");
    }

    public static function getKeys(string $pattern)
    {
        return self::$redis->keys($pattern);
    }

    private static function removePrefix(string $key)
    {
        return explode(".", $key)[1];
    }

    public static function forEach(string $pattern, callable $callback)
    {
        foreach (self::getKeys($pattern) as $key) {
            $callback(self::get(self::removePrefix($key)));
        }
    }

    public static function clear()
    {
        self::$redis->flushAll();
    }

    public static function subscribe(array $channels, callable $callback)
    {
        foreach ($channels as $channel) {
            self::$channels[$channel] = $callback;
        }
    }

    public static function publish(string $channel, string $message)
    {
        self::set($channel . ":" . self::$index, $message);
        self::forEach($channel . ":*", self::$channels[$channel]);
    }
}