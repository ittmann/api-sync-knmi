<?php

namespace App\Middleware;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;

/**
 * Driver wrapper for Windows Authentication on SQL Server
 */
class SqlSrvMiddleware implements Middleware
{
    public function wrap(Driver $driver): Driver
    {
        return new EmptyPortIfNeededDriver($driver);
    }
}
