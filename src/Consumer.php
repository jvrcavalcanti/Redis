<?php

namespace Accolon\Redis;

use Closure;

abstract class Consumer
{
    abstract public function handle($message);

    public function listen()
    {
        $channel = $this->signature ?? "";
        
        Redis::subscribe([$channel], Closure::fromCallable([$this, "handle"]));
    }
}