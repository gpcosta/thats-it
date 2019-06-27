<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 20/06/2019
 * Time: 15:46
 */

namespace ThatsIt\Command\VerifyCommand;

use ThatsIt\Command\Command;
use ThatsIt\Command\VerifyCommand\State\VerifyInitialState;

/**
 * Class InitCommand
 * @package ThatsIt\Command\VerifyCommand
 */
class VerifyCommand extends Command
{
    /**
     * @param int $argc
     * @param array $argv
     */
    public function performCommand(int $argc, array $argv): void
    {
        $this->setState(new VerifyInitialState($this));
        while ($this->state != null) {
            $this->state->perform();
            $this->setState($this->state->getNextState());
        }
    }
}