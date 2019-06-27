<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 24/06/2019
 * Time: 12:37
 */

namespace ThatsIt\Configurations;

/**
 * Class Configurations
 * @package ThatsIt\Configurations
 */
class Configurations
{
    /**
     * @return string
     */
    public static function getGeneralConfigFolder(): string
    {
        return __DIR__.'/../../config';
    }
    
    /**
     * @return string
     */
    public static function getGeneralConfigFile(): string
    {
        return self::getGeneralConfigFolder().'/config.php';
    }
    
    /**
     * @return string
     */
    public static function getDatabaseConfigFile(): string
    {
        return self::getGeneralConfigFolder().'/database.php';
    }
    
    /**
     * @return string
     */
    public static function getRoutesConfigFile(): string
    {
        return self::getGeneralConfigFolder().'/router.php';
    }
    
    /**
     * @return array
     */
    public static function getGeneralConfig(): array
    {
        return require(self::getGeneralConfigFile());
    }
    
    /**
     * @return array
     */
    public static function getDatabaseConfig(): array
    {
        return require(self::getDatabaseConfigFile());
    }
    
    /**
     * @return array
     */
    public static function getRoutesConfig(): array
    {
        return require(self::getRoutesConfigFile());
    }
}