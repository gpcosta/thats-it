<?php

namespace ThatsIt\Database;

use \PDO;
use ThatsIt\Configurations\Configurations;

/**
 * Class Database
 * @package ThatsIt\Database
 */
class Database
{
    private $pdo;
    
    /**
     * Database constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $config = Configurations::getDatabaseConfig();

        // PDO::ATTR_PERSISTENT = true means that the connection established will
        // be cached and re-used when another script requests a connection using
        // the same credentials
        $this->pdo = new PDO('mysql:host='.$config['host'].';port='.$config['port'].
            ';dbname='.$config['dbName'].';charset=utf8mb4', $config['user'], $config['password'], array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => true,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'"
        ));
    }

    public function getPDO()
    {
        return $this->pdo;
    }
}
