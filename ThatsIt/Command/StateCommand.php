<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 20/06/2019
 * Time: 15:49
 */

namespace ThatsIt\Command;

/**
 * Interface StateCommand
 * @package ThatsIt\Command
 */
abstract class StateCommand
{
    private $command;
    
    public function __construct(Command $command)
    {
        $this->command = $command;
    }
    
    public function getCommand(): Command
    {
        return $this->command;
    }
    
    abstract public function getName(): string;
    abstract public function perform(): void;
    
    public function getNextState(): ?StateCommand
    {
        return null;
    }
}