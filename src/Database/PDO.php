<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 12/12/2021
 * Time: 18:26
 */

namespace ThatsIt\Database;

/**
 * Class PDO
 * @package ThatsIt\Database
 */
class PDO extends \PDO
{
    private $howManyTransactions = 0;
    
    public function beginTransaction()
    {
        if (!$this->inTransaction())
            parent::beginTransaction();
        $this->howManyTransactions++;
    }
    
    public function commit()
    {
        $this->howManyTransactions--;
        if ($this->howManyTransactions === 0 && $this->inTransaction())
            parent::commit();
    }
    
    public function rollBack()
    {
        if ($this->inTransaction())
            parent::rollBack();
        $this->howManyTransactions = 0;
    }
}