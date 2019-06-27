<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 24/06/2019
 * Time: 12:06
 */

namespace ThatsIt\Logger;

use Monolog\Handler\FilterHandler;
use Monolog\Handler\StreamHandler;

/**
 * Class Logger
 * @package ThatsIt\Logger
 */
class Logger extends \Monolog\Logger
{
    /**
     * Logger constructor.
     * @param string $name
     * @throws \Exception
     */
    public function __construct(string $name)
    {
        $debugStreamHandler = new StreamHandler(__DIR__."/../../log/debug.log", Logger::DEBUG);
        $infoStreamHandler = new StreamHandler(__DIR__."/../../log/info.log", Logger::INFO);
        $warningStreamHandler = new StreamHandler(__DIR__."/../../log/warning.log", Logger::WARNING);
        $errorStreamHandler = new StreamHandler(__DIR__."/../../log/error.log", Logger::ERROR);
    
        $debugFilterHandler = new FilterHandler($debugStreamHandler, Logger::DEBUG, Logger::DEBUG);
        $infoFilterHandler = new FilterHandler($infoStreamHandler, Logger::INFO, Logger::INFO);
        
        // Create filter handler to make sure info stream only logs info events
        // Pass in the info handler
        // Notice is the minimum level this handler will handle
        // Warning is the maximum level this handler will handle
        $warningFilterHandler = new FilterHandler(
            $warningStreamHandler,
            Logger::NOTICE,
            Logger::WARNING
        );
        
        $handlers = array($debugFilterHandler, $infoFilterHandler, $warningFilterHandler, $errorStreamHandler);
        $processors = array();
        
        parent::__construct($name, $handlers, $processors);
    }
}