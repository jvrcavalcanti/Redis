<?php

use Accolon\Redis\Consumer;

class UserConsumer extends Consumer
{
    protected $signature = "user";

    public function handle($message)
    {
        var_dump($message);
    }
}