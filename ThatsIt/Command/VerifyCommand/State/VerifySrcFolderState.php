<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 25/06/2019
 * Time: 10:19
 */

namespace ThatsIt\Command\VerifyCommand\State;

use ThatsIt\Command\StateCommand;
use ThatsIt\Configurations\Configurations;
use ThatsIt\Folder\Folder;

/**
 * Class VerifySrcFolderState
 * @package ThatsIt\Command\VerifyCommand\State
 */
class VerifySrcFolderState extends StateCommand
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
        $folder = Folder::getSourceFolder();
        $this->verifySrcFolder($folder);
        $this->verifyControllerFolder($folder);
        $this->verifyPublicFolder();
        $this->verifyViewFolder($folder);
    }
    
    /**
     * @return null|StateCommand
     */
    public function getNextState(): ?StateCommand
    {
        return new VerifyFinalState($this->getCommand());
    }
    
    /**
     * @param string $folder
     * @throws \Exception
     */
    private function verifySrcFolder(string $folder): void
    {
        if (!is_dir($folder) && !mkdir($folder)) {
            throw new \Exception("It was not possible to create " . basename($folder) . " folder." . PHP_EOL);
        } else {
            print_r("\t-" . basename($folder) . PHP_EOL);
        }
    }
    
    /**
     * @param string $previousFolder
     * @throws \Exception
     */
    private function verifyControllerFolder(string $previousFolder): void
    {
        if (!is_dir($previousFolder . "/Controller") && !mkdir($previousFolder . "/Controller")) {
            throw new \Exception("It was not possible to create ".$previousFolder."/Controller folder." . PHP_EOL);
        } else {
            print_r("\t|-------Controller" . PHP_EOL);
        }
    }
    
    /**
     * @throws \Exception
     */
    private function verifyPublicFolder(): void
    {
        $generalConfig = Configurations::getGeneralConfig();
        if (!isset($generalConfig['locationServer'])) {
            throw new \Exception(
                "You need to indicate the location of the server in config/config.php (field locationServer).");
        }
        $folder = $generalConfig['locationServer'];
        
        if (!is_dir($folder) && !mkdir($folder, 0777, true)) {
            throw new \Exception("It was not possible to create ".$folder." folder." . PHP_EOL);
        } else {
            print_r("\t|-------".basename($folder).PHP_EOL);
        }
    
        $this->verifyPublicIndexFile($folder);
    }
    
    /**
     * @param string $previousFolder
     * @throws \Exception
     */
    private function verifyPublicIndexFile(string $previousFolder): void
    {
        // TODO: see where public folder content should be (js and css for now can only be at locationServer folder)
        if (!is_file($previousFolder."/index.php")
            && file_put_contents($previousFolder."/index.php", $this->getIndexContent()) === false
        ) {
            throw new \Exception("It was not possible to create ".$previousFolder."/index.php.".PHP_EOL);
        } else {
            print_r("\t|---------------index.php" . PHP_EOL);
        }
    }
    
    /**
     * @param string $previousFolder
     * @throws \Exception
     */
    private function verifyViewFolder(string $previousFolder): void
    {
        if (!is_dir($previousFolder . "/View") && !mkdir($previousFolder . "/View")) {
            throw new \Exception("It was not possible to create " . $previousFolder . "/View folder.".PHP_EOL);
        } else {
            print_r("\t|-------View".PHP_EOL);
        }
        
        $this->verifyErrorFolder($previousFolder."/View");
    }
    
    /**
     * @param string $previousFolder
     * @throws \Exception
     */
    private function verifyErrorFolder(string $previousFolder): void
    {
        $folder = $previousFolder."/Error";
        if (!is_dir($folder) && !mkdir($folder)) {
            throw new \Exception("It was not possible to create " . $folder . " folder.".PHP_EOL);
        } else {
            print_r("\t|---------------Error".PHP_EOL);
        }
    
        $this->verifyErrorFile($folder, 404);
        $this->verifyErrorFile($folder, 405);
        $this->verifyErrorFile($folder, 500);
    }
    
    /**
     * @param string $previousFolder
     * @param int $error
     * @throws \Exception
     */
    private function verifyErrorFile(string $previousFolder, int $error): void
    {
        if (!is_file($previousFolder."/error".$error.".php")
            && file_put_contents($previousFolder."/error".$error.".php",
                $this->getErrorContent($error)
            ) === false
        ) {
            throw new \Exception("It was not possible to create ".$previousFolder."/error".$error.".php.".PHP_EOL);
        } else {
            print_r("\t|-----------------------error".$error.".php" . PHP_EOL);
        }
    }
    
    /**
     * @return string
     */
    private function getIndexContent(): string
    {
        return "<?php".PHP_EOL.PHP_EOL.
            "require_once '".Folder::getThatsItFolder()."/EntryPoint/index.php';";
    }
    
    private function getErrorContent(int $error): string
    {
        return "<?php".PHP_EOL.
            PHP_EOL.
            "// you have available the variable \$error that".PHP_EOL.
            "// is populated with the message from Exception".PHP_EOL.
            PHP_EOL.
            "?>".PHP_EOL.
            "<html>".PHP_EOL.
                "\t<div>".PHP_EOL.
                    "\t\tError ".$error."!<br>".PHP_EOL.
                    "\t\t<?php echo \$error; ?>".PHP_EOL.
                "\t</div>".PHP_EOL.
            "</html>";
    }
}