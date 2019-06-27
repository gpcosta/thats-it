<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 20/06/2019
 * Time: 16:16
 */

namespace ThatsIt\Command\EmptyCommand\State;

use ThatsIt\Command\ListCommand\State\ListState;
use ThatsIt\Command\StateCommand;

class WelcomeState extends StateCommand
{
    public function getName(): string
    {
        return "Welcome";
    }
    
    public function perform(): void
    {
        print_r(
            'This is the "That\'s It" framework. '.
            'Our job is to be a very simple and lightweight framework '.
            'that allow users to setup the needed environment in just one minute. '.
            'If you don\'t believe in it, go ahead and try.'.PHP_EOL
        );
    }
    
    /**
     * @return null|StateCommand
     */
    public function getNextState(): ?StateCommand
    {
        return new ListState($this->getCommand());
    }
}