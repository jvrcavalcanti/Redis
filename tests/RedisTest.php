<?php

require "./vendor/autoload.php";
require "./tests/UserConsumer.php";

use Accolon\Redis\Redis;
use PHPUnit\Framework\TestCase;

class RedisTest extends TestCase
{
    public function setUp(): void
    {
        Redis::connect();
    }

    public function testSet()
    {
        Redis::clear();
        Redis::set("foo", "bar");
        Redis::set("bar", "foo");
        $this->assertTrue(true);
    }

    public function testGet()
    {
        $this->assertEquals(
            "bar",
            Redis::get("foo")
        );
    }

    public function testGet2()
    {
        $this->assertIsArray(Redis::get(["foo", "bar"]));
    }

    public function testHas()
    {
        $this->assertTrue(Redis::has("foo"));
    }

    public function testAllKeys()
    {
        $this->assertIsArray(Redis::allKeys());
    }

    public function testForEach()
    {
        Redis::set("test:1", "foo");
        Redis::set("test:2", "bar");

        Redis::forEach("test:*", fn($message) => $message);

        $this->assertTrue(true);
    }

    public function testPubSub()
    {
        Redis::subscribe(["test"], function ($msg) {
            echo $msg . PHP_EOL;
        });

        Redis::publish("test", "foobar");
        Redis::publish("test", "oi");

        $this->assertTrue(true);
    }

    /* public function testPubSub2()
    {
        $consumer = new UserConsumer();
        $consumer->listen();

        Redis::publish("user", "oi");

        $this->assertTrue(true);
    } */

    public function testDel()
    {
        Redis::del("foo");
        $this->assertFalse(Redis::has("foo"));
    }
}
