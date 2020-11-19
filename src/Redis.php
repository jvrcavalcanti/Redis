<?php

namespace Accolon\Redis;

class Redis
{
    private static \Redis $instance;
    private static array $channels = [];

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
        static::$instance->setOption(\Redis::OPT_PREFIX, "accolon.");
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

    public static function set(string $key, string $value, int $time = 100)
    {
        return static::$instance->pSetEx($key, $time, $value);
    }

    public static function has(string $key)
    {
        return (bool) static::$instance->exists($key);
    }

    public static function del($keys)
    {
        return static::$instance->del($keys);
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

    private static function removePrefix(string $key)
    {
        return explode(".", $key)[1];
    }

    public static function forEach(string $pattern, callable $callback)
    {
        foreach (static::getKeys($pattern) as $key) {
            $callback(static::get(static::removePrefix($key)));
        }
    }

    public static function clear()
    {
        static::$instance->flushAll();
    }

    public static function subscribe(array $channels, callable $callback)
    {
        foreach ($channels as $channel) {
            static::$channels[$channel] = $callback;
        }
    }

    public static function publish(string $channel, string $message)
    {
        $index = md5(microtime(true));
        static::set($channel . ":" . $index, $message);
        static::forEach($channel . ":*", static::$channels[$channel]);
    }
}
