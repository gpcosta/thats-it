<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 27/06/2019
 * Time: 18:54
 */

namespace ThatsIt\EntryPoint;

use ThatsIt\Configurations\Configurations;
use ThatsIt\Exception\PlatformException;
use ThatsIt\Folder\Folder;
use ThatsIt\Request\HttpRequest;

/**
 * Class Door
 * @package ThatsIt\EntryPoint
 *
 * class that will be the first contact by the index.php that will be in server
 * this class will connect this index.php to EntryPoint
 */
class Door
{
    /**
     * @param string $indexPath
     * @throws PlatformException
     * @throws \ThatsIt\Request\Exception\MissingRequestMetaVariableException
     */
    public static function openDoor(string $indexPath): void
    {
        Folder::setIndexPath($indexPath);
        
        set_error_handler(function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                // This error code is not included in error_reporting
                return;
            }
            throw new \ErrorException($message, 500, $severity, $file, $line);
        });
        
        $generalConfig = Configurations::getGeneralConfig();
        $environment = $generalConfig['environment'];
        try {
            if ($environment !== 'production') {
                /**
                 * Register the error handler
                 */
                $whoops = new \Whoops\Run();
                $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
                $whoops->register();
            }
        } catch (\Exception $e) {
            // $whoops never will be needed in production, just for debug purpose
        }
    
        $entryPoint = new EntryPoint(
            new HttpRequest($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER, file_get_contents('php://input')),
            $environment
        );
        $entryPoint->callController();
    }
}