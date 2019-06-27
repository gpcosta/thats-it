<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 20/06/2019
 * Time: 16:16
 */

namespace ThatsIt\Command\ListCommand\State;

use ThatsIt\Command\StateCommand;

/**
 * Class ListState
 * @package ThatsIt\Command\ListCommand\State
 */
class ListState extends StateCommand
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return "List";
    }
    
    public function perform(): void
    {
        $possibleCommands = json_decode(file_get_contents("ThatsIt/Command/commands.json"), true);
        print_r("\nPossible Commands:\n");
        foreach ($possibleCommands as $command => $className) {
            if ($command) print_r("\t- ".$command."\n");
        }
    }
}