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
	const ENVIRONMENT_DEV = "development";
	const ENVIRONMENT_PROD = "production";
	
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
	 * @return string
	 * @throws PlatformException
	 */
    public static function getEnvironment(): string
	{
		$config = self::getGeneralConfig();
		if (!array_key_exists('environment', $config))
			throw new PlatformException('There is no environment defined in config/config.php',
				PlatformException::ERROR_CONFIG_LINE_MISSING);
		return $config['environment'];
	}
	
	/**
	 * @return string
	 * @throws PlatformException
	 */
    public static function getLocationServer(): string
	{
		$config = self::getGeneralConfig();
		if (!array_key_exists('environment', $config))
			throw new PlatformException('There is no environment defined in config/config.php',
				PlatformException::ERROR_CONFIG_LINE_MISSING);
		return $config['environment'];
	}
	
	/**
	 * @return string
	 * @throws PlatformException
	 */
	public static function getDomain(): string
	{
		$config = self::getGeneralConfig();
		if (!array_key_exists('domain', $config))
			throw new PlatformException('There is no domain defined in config/config.php',
				PlatformException::ERROR_CONFIG_LINE_MISSING);
		return $config['domain'];
	}
	
    /**
     * Get configuration used by Twitter Snowflake ID
     *
     * @return array - ['datacenterId' => int|null, 'workerId' => int|null]
     * @throws PlatformException
     */
    public static function getSnowflakeConfig(): array
    {
        $config = self::getGeneralConfig();
		if (!array_key_exists('datacenterId', $config))
			throw new PlatformException('There is no datacenterId defined in config/config.php',
				PlatformException::ERROR_CONFIG_LINE_MISSING);
		if (!array_key_exists('workerId', $config))
			throw new PlatformException('There is no workerId defined in config/config.php',
				PlatformException::ERROR_CONFIG_LINE_MISSING);
        return [
			'datacenterId' => $config['datacenterId'],
			'workerId' => $config['workerId']
		];
    }
	
	/**
	 * @return string
	 * @throws PlatformException
	 */
	public static function getFallbackLocale(): string
	{
		$config = self::getGeneralConfig();
		if (!array_key_exists('fallbackLocale', $config))
			throw new PlatformException('There is no fallbackLocale defined in config/config.php',
				PlatformException::ERROR_CONFIG_LINE_MISSING);
		return $config['fallbackLocale'];
	}
	
	/**
	 * @param string ...$fields - each field is a level in the tree of config
	 * 							   $config[$field1][$field2][$field3]...
	 * @return mixed
	 * @throws PlatformException
	 */
	public static function getFieldFromConfig(string ...$fields)
	{
		$value = self::getGeneralConfig();
		foreach ($fields as $field) {
			if (!array_key_exists($field, $value))
				throw new PlatformException('There is no ['.implode('][', $fields).'] defined in config/config.php',
					PlatformException::ERROR_CONFIG_LINE_MISSING);
			$value = $value[$field];
		}
		return $value;
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
	
	/**
	 * @param string $configFilename
	 * @param string ...$fields
	 * @return mixed
	 * @throws PlatformException
	 *
	 * @see self::getFieldFromConfig
	 */
    public static function getFieldFromOtherConfig(string $configFilename, string ...$fields)
	{
		$filepath = Folder::getGeneralConfigFolder().'/'.$configFilename.'.php';
		if (!is_file($filepath)) {
			throw new PlatformException(
				'No '.$configFilename.' file. It\'s missing the file config/'.$configFilename.'.php.',
				PlatformException::ERROR_CONFIG_FILE_MISSING
			);
		}
		
		$value = require_once $filepath;
		foreach ($fields as $field) {
			if (!array_key_exists($field, $value))
				throw new PlatformException(
					'There is no ['.implode('][', $fields).'] defined in config/'.$configFilename.'.php',
					PlatformException::ERROR_CONFIG_LINE_MISSING
				);
			$value = $value[$field];
		}
		return $value;
	}
}