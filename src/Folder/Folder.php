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
     * @return string
     */
    public static function getSourceFolder(): string
    {
        return getcwd()."/../../src";
    }
    
    /**
     * @return string
     */
    public static function getGeneralConfigFolder(): string
    {
        return getcwd().'/../../config';
    }
}