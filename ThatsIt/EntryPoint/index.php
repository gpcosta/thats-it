<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 26/06/2019
 * Time: 16:58
 */

require_once __DIR__.'/../../vendor/autoload.php';

set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return;
    }
    throw new \ErrorException($message, 500, $severity, $file, $line);
});

function sendErrorMessage(int $statusCode, array $message)
{
    $response = new \ThatsIt\Response\JsonResponse();
    $response->setStatusCode($statusCode);
    $response->setVariables($message);
    $send = new \ThatsIt\Response\SendResponse($response);
    $send->send();
}

$logger = null;
try {
    $logger = new \ThatsIt\Logger\Logger('ThatsIt');
} catch (\Exception $e) {
    sendErrorMessage(500, [
        "error" => "The service that you are trying to contact cannot fulfill your request at the moment."
    ]);
}

if (\ThatsIt\Configurations\Configurations::getGeneralConfig()['environment'] !== 'production') {
    try {
        /**
         * Register the error handler
         */
        $whoops = new \Whoops\Run();
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
        $whoops->register();
    } catch (\Exception $e) {
        sendErrorMessage(500, [
            "error" => "filp/whoops cannot load."
        ]);
    }
}

try {
    $entryPoint = new \ThatsIt\EntryPoint\EntryPoint(
        new \ThatsIt\Request\HttpRequest($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER, file_get_contents('php://input')),
        ThatsIt\Configurations\Configurations::getRoutesConfig(),
        $logger
    );
    $entryPoint->callController();
} catch (\ThatsIt\Exception\ClientException $e) {
    $error = $e->getMessage();
    if ($e->getCode() == 404)
        require_once(\ThatsIt\Folder\Folder::getSourceFolder().'/View/Error/error404.php');
    else if ($e->getCode() == 405)
        require_once(\ThatsIt\Folder\Folder::getSourceFolder().'/View/Error/error405.php');
    else
        require_once(\ThatsIt\Folder\Folder::getSourceFolder().'/View/Error/error500.php');
    die;
} catch (\ThatsIt\Exception\PlatformException | \ErrorException | \Exception $e) {
    if (\ThatsIt\Configurations\Configurations::getGeneralConfig()['environment'] === 'production') {
        sendErrorMessage(500, [
            "error" => "The service that you are trying to contact cannot fulfill your request at the moment."
        ]);
        if ($e instanceof \ThatsIt\Exception\PlatformException)
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
