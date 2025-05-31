<?php

namespace App\Log;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

class LoggerFactory
{
    public static function createLogger(string $channelName): Logger
    {
        $logger = new Logger($channelName);

        $logger->pushHandler(new StreamHandler('var/log/app.log', Level::Warning));

        return $logger;
    }
}
