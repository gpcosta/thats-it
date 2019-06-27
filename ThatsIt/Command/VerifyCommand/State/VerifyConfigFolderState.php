<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 25/06/2019
 * Time: 10:02
 */

namespace ThatsIt\Command\VerifyCommand\State;

use ThatsIt\Command\StateCommand;
use ThatsIt\Configurations\Configurations;
use ThatsIt\Folder\Folder;

/**
 * Class VerifyConfigFolderState
 * @package ThatsIt\Command\VerifyCommand\State
 */
class VerifyConfigFolderState extends StateCommand
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return "Verify config folder structure";
    }
    
    /**
     * @throws \Exception
     */
    public function perform(): void
    {
        $this->verifyConfigFolder();
        $this->verifyConfigFile();
        $this->verifyRouterFile();
        $this->verifyDatabaseFile();
    }
    
    /**
     * @return null|StateCommand
     */
    public function getNextState(): ?StateCommand
    {
        return new VerifySrcFolderState($this->getCommand());
    }
    
    /**
     * @throws \Exception
     */
    private function verifyConfigFolder(): void
    {
        if ((!is_dir(Configurations::getGeneralConfigFolder()) && !mkdir(Configurations::getGeneralConfigFolder()))) {
            throw new \Exception("It was not possible to create config folder." . PHP_EOL);
        } else {
            print_r("\t-config" . PHP_EOL);
        }
    }
    
    /**
     * @throws \Exception
     */
    private function verifyConfigFile(): void
    {
        if (!is_file(Configurations::getGeneralConfigFile())
            && file_put_contents(Configurations::getGeneralConfigFile(), $this->getGeneralConfigExample()) === false
        ) {
            throw new \Exception("It was not possible to create config/config.php." . PHP_EOL);
        } else {
            print_r("\t|-------config.php" . PHP_EOL);
        }
    }
    
    /**
     * @throws \Exception
     */
    private function verifyRouterFile(): void
    {
        if (!is_file(Configurations::getRoutesConfigFile())
            && file_put_contents(Configurations::getRoutesConfigFile(), $this->getRouterExample()) === false
        ) {
            throw new \Exception("It was not possible to create config/router.php.".PHP_EOL);
        } else {
            print_r("\t|-------router.php".PHP_EOL);
        }
    }
    
    /**
     * @throws \Exception
     */
    private function verifyDatabaseFile(): void
    {
        if (!is_file(Configurations::getDatabaseConfigFile())
            && file_put_contents(Configurations::getDatabaseConfigFile(), $this->getDatabaseConfigExample()) === false
        ) {
            throw new \Exception("It was not possible to create config/database.php." . PHP_EOL);
        } else {
            print_r("\t|-------database.php" . PHP_EOL);
        }
    }
    
    /**
     * @return string
     */
    private function getGeneralConfigExample(): string
    {
        return "<?php".PHP_EOL.
            PHP_EOL.
            "return array(".PHP_EOL.
                "\t'environment' => 'development',".PHP_EOL.
                "\t'locationServer' => '".Folder::getSourceFolder()."/Public'".PHP_EOL.
            ");";
    }
    
    /**
     * @return string
     */
    private function getDatabaseConfigExample(): string
    {
        return "<?php".PHP_EOL.
            PHP_EOL.
            "return array(".PHP_EOL.
                "\t'host' => 'localhost',".PHP_EOL.
                "\t'port' => 3306,".PHP_EOL.
                "\t'name' => 'any_name',".PHP_EOL.
                "\t'user' => 'any_user',".PHP_EOL.
                "\t'password' => 'any_password'".PHP_EOL.
            ");";
    }
    
    /**
     * @return string
     */
    private function getRouterExample(): string
    {
        return "<?php".PHP_EOL.
            PHP_EOL.
            "return array(".PHP_EOL.
                "\t'example_name' => array(".PHP_EOL.
                    "\t\t'path' => '/path/to/example',".PHP_EOL.
                    "\t\t'httpMethods' => ['GET', 'POST'],".PHP_EOL.
                    "\t\t'controller' => 'Complete\\\\Name\\\\Of\\\\Controller',".PHP_EOL.
                    "\t\t'function' => 'functionToExecute',".PHP_EOL.
                    "\t\t'parameters' => array(".PHP_EOL.
                        "\t\t\t'name' => array(".PHP_EOL.
                            "\t\t\t\t'default' => 'hello world'".PHP_EOL.
                        "\t\t\t),".PHP_EOL.
                        "\t\t\t'page' => array(".PHP_EOL.
                            "\t\t\t\t'default' => 1".PHP_EOL.
                        "\t\t\t)".PHP_EOL.
                    "\t\t)".PHP_EOL.
                "\t)".PHP_EOL.
            ");";
    }
}