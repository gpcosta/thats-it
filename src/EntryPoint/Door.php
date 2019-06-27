<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 27/06/2019
 * Time: 18:54
 */

namespace ThatsIt\EntryPoint;

use ThatsIt\Configurations\Configurations;
use ThatsIt\Exception\ClientException;
use ThatsIt\Exception\PlatformException;
use ThatsIt\Folder\Folder;
use ThatsIt\Logger\Logger;
use ThatsIt\Request\HttpRequest;
use ThatsIt\Response\JsonResponse;
use ThatsIt\Response\SendResponse;

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
     * @throws PlatformException
     * @throws \ErrorException
     */
    public static function openDoor(): void
    {
        set_error_handler(function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                // This error code is not included in error_reporting
                return;
            }
            throw new \ErrorException($message, 500, $severity, $file, $line);
        });
    
        $logger = null;
        try {
            $logger = new Logger('ThatsIt');
        } catch (\Exception $e) {
            self::sendErrorMessage(500, [
                "error" => "The service that you are trying to contact cannot fulfill your request at the moment."
            ]);
        }
        
        $environment = 'production';
        try {
            $environment = Configurations::getGeneralConfig()['environment'];
            if ($environment !== 'production') {
                /**
                 * Register the error handler
                 */
                $whoops = new \Whoops\Run();
                $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
                $whoops->register();
            }
        } catch (\Exception $e) {
            self::sendErrorMessage(500, [
                "error" => "filp/whoops cannot load."
            ]);
        }
    
        try {
            $entryPoint = new EntryPoint(
                new HttpRequest($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER, file_get_contents('php://input')),
                Configurations::getRoutesConfig(),
                $logger
            );
            $entryPoint->callController();
        } catch (ClientException $e) {
            $error = $e->getMessage();
            if ($e->getCode() == 404)
                require_once(Folder::getSourceFolder().'/View/Error/error404.php');
            else if ($e->getCode() == 405)
                require_once(Folder::getSourceFolder().'/View/Error/error405.php');
            else
                require_once(Folder::getSourceFolder().'/View/Error/error500.php');
            die;
        } catch (PlatformException | \ErrorException | \Exception $e) {
            if ($environment === 'production') {
                self::sendErrorMessage(500, [
                    "error" => "The service that you are trying to contact cannot fulfill your request at the moment."
                ]);
                if ($e instanceof PlatformException)
                    $logger->addError($e->getMessage(), ["code" => $e->getCode(), "exception" => $e]);
                else if ($e instanceof \ErrorException)
                    $logger->addEmergency($e->getMessage(), ["code" => $e->getCode(), "exception" => $e]);
                else
                    $logger->addAlert($e->getMessage(), ["code" => $e->getCode(), "exception" => $e]);
            } else {
                // catch by $whoops
                throw $e;
            }
        }
    }
    
    /**
     * @param int $statusCode
     * @param array $message
     */
    public static function sendErrorMessage(int $statusCode, array $message): void
    {
        $response = new JsonResponse();
        $response->setStatusCode($statusCode);
        $response->setVariables($message);
        $send = new SendResponse($response);
        $send->send();
    }
}