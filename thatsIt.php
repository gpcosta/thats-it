<?php

namespace ThatsIt;

require_once 'vendor/autoload.php';

/**
 * Class CliClient
 * @package ThatsIt
 */
class CliClient
{
    /**
     * @param int $argc
     * @param array $argv
     */
    function performCommand(int $argc, array $argv)
    {
        $possibleCommands = json_decode(file_get_contents("ThatsIt/Command/commands.json"), true);
        
        try {
            if ($argc == 1) {
                $command = new $possibleCommands['']();
                $command->performCommand($argc, $argv);
            } else if ($argc > 1) {
                if (isset($possibleCommands[$argv[1]])) {
                    $command = new $possibleCommands[$argv[1]]();
                    $command->performCommand($argc, $argv);
                } else {
                    $command = new $possibleCommands['']();
                    $command->performCommand($argc, $argv);
                }
            }
        } catch (\Exception $e) {
            print_r($e->getMessage()."\n");
        }
    }
}

if (php_sapi_name() == "cli") {
    // In cli-mode
    
    $cliClient = new CliClient();
    $cliClient->performCommand($argc, $argv);
}