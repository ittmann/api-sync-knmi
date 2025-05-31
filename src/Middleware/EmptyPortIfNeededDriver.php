<?php

namespace App\Middleware;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;

/**
 * Clears the doctrine dbal port config when Doctrine\DBAL\Driver\PDO\SQLSrv\Driver is used
 * This is needed to get Windows Authentication (userless/passwordless) working
 */
final class EmptyPortIfNeededDriver extends AbstractDriverMiddleware
{
    public function connect(array $params): Connection
    {
        if (
            isset($params['user']) && $params['user'] === '' &&
            isset($params['password']) && $params['password'] === '' &&
            isset($params['driverClass']) && $params['driverClass'] === '\Doctrine\DBAL\Driver\PDO\SQLSrv\Driver'
        ) {
            unset($params['port']);
        }

        return parent::connect($params);
    }
}
