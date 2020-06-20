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
    private static $generalConfig = null;
    private static $databaseConfig = null;
    private static $routesConfig = null;
    
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
     * Return general config. If some file is passed as argument,
     * this file will be loaded as general config instead of default general config file
     *
     * If already called this method once, the general config used at
     * the last time is already loaded in memory
     * When calling this method for the second time (without parameters),
     * the previous returned config will be returned
     *
     * If this method is called with parameters, the config file will be reloaded
     *
     * @note configurations are hold by a static variable.
     *       If you cause a reload of configurations, everywhere where
     *       you use the array returned by this method will be affected
     *
     * @param string|null $generalConfigPath
     * @return array
     * @throws PlatformException
     */
    public static function getGeneralConfig(string $generalConfigPath = null): array
    {
        if ($generalConfigPath !== null || self::$generalConfig === null) {
            if ($generalConfigPath === null) $generalConfigPath = self::getGeneralConfigFile();
            if (!is_file($generalConfigPath)) {
                throw new PlatformException("No general config file. It's missing the file config/config.php.",
                    PlatformException::ERROR_CONFIG_FILE_MISSING);
            }
            self::$generalConfig = require($generalConfigPath);
        }
        return self::$generalConfig;
    }
    
    /**
     * Get configuration used by Twitter Snowflake ID
     *
     * @return array - ['datacenterId' => int|null, 'workerId' => int|null]
     * @throws PlatformException
     */
    public static function getSnowflakeConfig(): array
    {
        $generalConfig = self::getGeneralConfig();
        $config = [];
        $config['datacenterId'] = (
        array_key_exists('datacenterId', $generalConfig) ? $generalConfig['datacenterId'] : null
        );
        $config['workerId'] = (
        array_key_exists('workerId', $generalConfig) ? $generalConfig['workerId'] : null
        );
        return $config;
    }
    
    /**
     * Return database config. If some file is passed as argument,
     * this file will be loaded as database config instead of default database config file
     *
     * If already called this method once, the general config used at
     * the last time is already loaded in memory
     * When calling this method for the second time (without parameters),
     * the previous returned config will be returned
     *
     * If this method is called with parameters, the config file will be reloaded
     *
     * @note configurations are hold by a static variable.
     *       If you cause a reload of configurations, everywhere where
     *       you use the array returned by this method will be affected
     *
     * @param string|null $configDBPath
     * @return array
     * @throws PlatformException
     */
    public static function getDatabaseConfig(string $configDBPath = null): array
    {
        if ($configDBPath !== null || self::$databaseConfig === null) {
            if ($configDBPath === null) $configDBPath = self::getDatabaseConfigFile();
            if (!is_file($configDBPath)) {
                throw new PlatformException("No database file. It's missing the file config/database.php.",
                    PlatformException::ERROR_CONFIG_FILE_MISSING);
            }
            self::$databaseConfig = require($configDBPath);
        }
        return self::$databaseConfig;
    }
    
    /**
     * Return routes config. If some file is passed as argument,
     * this file will be loaded as routes config instead of default routes config file
     *
     * If already called this method once, the general config used at
     * the last time is already loaded in memory
     * When calling this method for the second time (without parameters),
     * the previous returned config will be returned
     *
     * If this method is called with parameters, the config file will be reloaded
     *
     * @note configurations are hold by a static variable.
     *       If you cause a reload of configurations, everywhere where
     *       you use the array returned by this method will be affected
     *
     * @param string|null $routesPath
     * @return array
     * @throws PlatformException
     */
    public static function getRoutesConfig(string $routesPath = null): array
    {
        if ($routesPath !== null || self::$routesConfig === null) {
            if ($routesPath === null) $routesPath = self::getRoutesConfigFile();
            if (!is_file($routesPath)) {
                throw new PlatformException("No routes file. It's missing the file config/router.php.",
                    PlatformException::ERROR_CONFIG_FILE_MISSING);
            }
            self::$routesConfig = require($routesPath);
        }
        return self::$routesConfig;
    }
}