<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 25/06/2019
 * Time: 11:06
 */

namespace ThatsIt\Command\VerifyCommand\State;

use ThatsIt\Command\StateCommand;

/**
 * Class VerifyFinalState
 * @package ThatsIt\Command\VerifyCommand\State
 */
class VerifyFinalState extends StateCommand
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
            PHP_EOL.
            "All files were verified and created if not existed. Now you can configure them as you want.".
            PHP_EOL
        );
    }
    
    /**
     * @return null|StateCommand
     */
    public function getNextState(): ?StateCommand
    {
        return null;
    }
}