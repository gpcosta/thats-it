<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 25/06/2019
 * Time: 10:29
 */

namespace ThatsIt\Folder;

/**
 * Class Folder
 * @package ThatsIt\SourceFolder
 */
class Folder
{
    /**
     * @var string
     */
    private static $indexPath;
    
    /**
     * @param string $path
     */
    public static function setIndexPath(string $path): void
    {
        // if the last char of $path is "/", remove it
        if (substr($path, strlen($path) - 1, 1) == "/") {
            $path = substr($path, 0, strlen($path) - 1);
        }
        self::$indexPath = $path;
    }
    
    /**
     * @return string
     */
    public static function getSourceFolder(): string
    {
        // alternative: self::$indexPath."/../../src";
        return self::$indexPath."/..";
    }
    
    /**
     * @return string
     */
    public static function getPublicFolder(): string
    {
        return self::$indexPath;
    }
    
    /**
     * @return string
     */
    public static function getGeneralConfigFolder(): string
    {
        return self::$indexPath.'/../../config';
    }
    
    /**
     * @return string
     */
    public static function getLogFolder(): string
    {
        return self::$indexPath.'/../../log';
    }
    
    /**
     * @return string
     */
    public static function getTranslationsFolder(): string
    {
        return self::getSourceFolder().'/translations';
    }
}