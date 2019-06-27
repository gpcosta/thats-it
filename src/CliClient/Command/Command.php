<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 20/06/2019
 * Time: 15:41
 */

namespace ThatsIt\CliClient\Command;

/**
 * Class Command
 * @package ThatsIt\Command
 */
abstract class Command
{
    /**
     * @var StateCommand
     */
    protected $state;
    
    /**
     * @param int $argc
     * @param array $argv
     */
    abstract public function performCommand(int $argc, array $argv): void;
    
    /**
     * @param null|StateCommand $state
     * @return Command
     */
    public function setState(?StateCommand $state): Command
    {
        $this->state = $state;
        return $this;
    }
}