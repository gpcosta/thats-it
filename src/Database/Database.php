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
     * @throws PlatformException
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
                $this->pdo = new PDO('mysql:host=' . $config['host'] . ';port=' . $config['port'] .
                    ';dbname=' . $config['dbName'] . ';charset=' . $config['encoding'], $config['user'], $config['password'], array(
                        // all errors will cause a PDOException
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        // PDO::ATTR_PERSISTENT = true means that the connection established will
                        // be cached and re-used when another script requests a connection using
                        // the same credentials
                        PDO::ATTR_PERSISTENT => true,
                        // default fetch mode is set to PDO::FETCH_ASSOC
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        // emulation will be done by MySQL and not by PDO library
                        PDO::ATTR_EMULATE_PREPARES => false,
                        // set encoding and collation for the connection
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '" . $config['encoding'] . "' COLLATE '" . $config['collation'] . "'"
                    )
                );
            }
        } catch (PlatformException $e) {
            throw $e;
        } catch (\PDOException $e) {
            $this->exceptionAtTheBeginning = new PlatformException("There was a problem connecting to DB. ".
                "Please verify if config/database.php has the correct info.", PlatformException::ERROR_DB, $e);
        } catch (\Exception $e) {
            $this->exceptionAtTheBeginning = new PlatformException("Something went wrong. Please try again later.",
                PlatformException::ERROR_DB, $e);
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
