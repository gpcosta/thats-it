<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 24/06/2019
 * Time: 08:56
 */

namespace ThatsIt\Command\VerifyCommand\State;

use ThatsIt\Command\StateCommand;

/**
 * Class VerifyInitialState
 * @package ThatsIt\Command\VerifyCommand\State
 */
class VerifyInitialState extends StateCommand
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return "Verify files and folders structure";
    }
    
    /**
     * @throws \Exception
     */
    public function perform(): void
    {
        print_r(
            "Verifying files and folders structure.".PHP_EOL
        );
    }
    
    /**
     * @return null|StateCommand
     */
    public function getNextState(): ?StateCommand
    {
        return new VerifyConfigFolderState($this->getCommand());
    }
}