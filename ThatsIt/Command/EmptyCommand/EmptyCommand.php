<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 20/06/2019
 * Time: 16:13
 */

namespace ThatsIt\Command\EmptyCommand;

use ThatsIt\Command\Command;
use ThatsIt\Command\EmptyCommand\State\WelcomeState;
use ThatsIt\Command\ListCommand\State\ListState;

/**
 * Class EmptyCommand
 * @package ThatsIt\Command
 */
class EmptyCommand extends Command
{
    /**
     * @param int $argc
     * @param array $argv
     */
    public function performCommand(int $argc, array $argv): void
    {
        $this->setState(new WelcomeState($this));
        while ($this->state != null) {
            $this->state->perform();
            $this->setState($this->state->getNextState());
        }
    }
}