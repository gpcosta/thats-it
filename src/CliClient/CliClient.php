<?php

namespace ThatsIt\CliClient;

use ThatsIt\CliClient\Command\VerifyCommand\VerifyCommand;

/**
 * Class CliClient
 * @package ThatsIt
 */
class CliClient
{
    /**
     * method to be called when is run "composer create-project"
     */
    public static function createProject(): void
    {
        $command = new VerifyCommand();
        $command->performCommand(1, array());
    }
    
    /**
     * @param int $argc
     * @param array $argv
     */
    public function performCommand(int $argc, array $argv)
    {
        $possibleCommands = json_decode(file_get_contents(__DIR__."/commands.json"), true);
        
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