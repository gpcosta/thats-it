<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 27/06/2019
 * Time: 18:29
 */

if (php_sapi_name() == "cli") {
    // In cli-mode
    
    $cliClient = new \ThatsIt\CliClient();
    $cliClient->performCommand($argc, $argv);
}