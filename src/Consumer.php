<?php

namespace Accolon\Redis;

abstract class Consumer
{
    abstract public function handle($message);

    public function listen()
    {
        $channel = $this->signature ?? strtolower(explode("Consumer", (new \ReflectionClass(static::class))->getShortName())[0]);
        
        Redis::subscribe([$channel], \Closure::fromCallable([$this, "handle"]));
    }
}
