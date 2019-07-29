<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 24/06/2019
 * Time: 12:37
 */

namespace ThatsIt\Configurations;

use ThatsIt\Exception\PlatformException;
use ThatsIt\Folder\Folder;

/**
 * Class Configurations
 * @package ThatsIt\Configurations
 */
class Configurations
{
    /**
     * @return string
     */
    public static function getGeneralConfigFile(): string
    {
        return Folder::getGeneralConfigFolder().'/config.php';
    }
    
    /**
     * @return string
     */
    public static function getDatabaseConfigFile(): string
    {
        return Folder::getGeneralConfigFolder().'/database.php';
    }
    
    /**
     * @return string
     */
    public static function getRoutesConfigFile(): string
    {
        return Folder::getGeneralConfigFolder().'/router.php';
    }
    
    /**
     * @param string|null $generalConfigPath
     * @return array
     * @throws PlatformException
     */
    public static function getGeneralConfig(string $generalConfigPath = null): array
    {
        if ($generalConfigPath === null) $generalConfigPath = self::getGeneralConfigFile();
        if (!is_file($generalConfigPath)) {
            throw new PlatformException("No general config file. It's missing the file config/config.php.",
                PlatformException::ERROR_CONFIG_FILE_MISSING);
        }
        return require($generalConfigPath);
    }
    
    /**
     * @param string|null $configDBPath
     * @return array
     * @throws PlatformException
     */
    public static function getDatabaseConfig(string $configDBPath = null): array
    {
        if ($configDBPath === null) $configDBPath = self::getDatabaseConfigFile();
        if (!is_file($configDBPath)) {
            throw new PlatformException("No database file. It's missing the file config/database.php.",
                PlatformException::ERROR_CONFIG_FILE_MISSING);
        }
        return require($configDBPath);
    }
    
    /**
     * @param string|null $routesPath
     * @return array
     * @throws PlatformException
     */
    public static function getRoutesConfig(string $routesPath = null): array
    {
        if ($routesPath === null) $routesPath = self::getRoutesConfigFile();
        if (!is_file($routesPath)) {
            throw new PlatformException("No routes file. It's missing the file config/router.php.",
                PlatformException::ERROR_CONFIG_FILE_MISSING);
        }
        return require($routesPath);
    }
}