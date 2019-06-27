<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 20/06/2019
 * Time: 16:13
 */

namespace ThatsIt\CliClient\Command\ListCommand;

use ThatsIt\CliClient\Command\Command;
use ThatsIt\CliClient\Command\ListCommand\State\ListState;

/**
 * Class ListCommand
 * @package ThatsIt\Command\ListCommand
 */
class ListCommand extends Command
{
    /**
     * @param int $argc
     * @param array $argv
     */
    public function performCommand(int $argc, array $argv): void
    {
        $this->setState(new ListState($this));
        $this->state->perform();
    }
}