<?php

namespace ThatsIt\Database;

use \PDO;
use ThatsIt\Configurations\Configurations;
use ThatsIt\Exception\PlatformException;

/**
 * Class Database
 * @package ThatsIt\Database
 */
class Database
{
    /**
     * @var PDO
     */
    private $pdo;
    
    /**
     * this var hold some exception that can happen when this object is trying to connect to DB
     * this exception will only be raised when a getPDO() is called
     * if the app doesn't need any connection to DB, it will never be raised
     *
     * @var \Exception
     */
    private $exceptionAtTheBeginning;
    
    /**
     * Database constructor.
     */
    public function __construct()
    {
        try {
            $config = Configurations::getDatabaseConfig();
        
            if (!isset($config['encoding'])) $config['encoding'] = "utf8mb4";
            if (!isset($config['collation'])) $config['collation'] = "utf8mb4_unicode_ci";
            
            $this->pdo = null;
            $this->exceptionAtTheBeginning = null;
            
            if (isset($config['host'], $config['port'], $config['dbName'], $config['password'])) {
                // PDO::ATTR_PERSISTENT = true means that the connection established will
                // be cached and re-used when another script requests a connection using
                // the same credentials
                $this->pdo = new PDO('mysql:host=' . $config['host'] . ';port=' . $config['port'] .
                    ';dbname=' . $config['dbName'] . ';charset=' . $config['encoding'], $config['user'], $config['password'], array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_PERSISTENT => true,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '" . $config['encoding'] . "' COLLATE '" . $config['collation'] . "'"
                ));
            }
        } catch (\Exception $e) {
            $this->exceptionAtTheBeginning = $e;
        }
    
    }
    
    /**
     * @return PDO
     * @throws PlatformException
     */
    public function getPDO()
    {
        if ($this->exceptionAtTheBeginning) {
            throw new PlatformException("There was an error creating PDO object. ".
                "The given info in config/database.php is probably incorrect.", PlatformException::ERROR_DB,
                $this->exceptionAtTheBeginning);
        } else if ($this->pdo == null) {
            throw new PlatformException("No PDO was created because the data provided is not enough.",
                PlatformException::ERROR_DB);
        }
        return $this->pdo;
    }
}
