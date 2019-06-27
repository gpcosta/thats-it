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
     * @return array
     * @throws PlatformException
     */
    public static function getGeneralConfig(): array
    {
        if (!is_file(self::getGeneralConfigFile())) {
            throw new PlatformException("No general config file. It's missing the file config/config.php.",
                PlatformException::ERROR_CONFIG_FILE_MISSING);
        }
        return require(self::getGeneralConfigFile());
    }
    
    /**
     * @return array
     * @throws PlatformException
     */
    public static function getDatabaseConfig(): array
    {
        if (!is_file(self::getDatabaseConfigFile())) {
            throw new PlatformException("No database file. It's missing the file config/database.php.",
                PlatformException::ERROR_CONFIG_FILE_MISSING);
        }
        return require(self::getDatabaseConfigFile());
    }
    
    /**
     * @return array
     * @throws PlatformException
     */
    public static function getRoutesConfig(): array
    {
        if (!is_file(self::getRoutesConfigFile())) {
            throw new PlatformException("No routes file. It's missing the file config/router.php.",
                PlatformException::ERROR_CONFIG_FILE_MISSING);
        }
        return require(self::getRoutesConfigFile());
    }
}