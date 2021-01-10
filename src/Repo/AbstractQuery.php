<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 31/12/2020
 * Time: 19:37
 */

namespace ThatsIt\Repo;

use PDO;
use PDOStatement;

/**
 * Class AbstractQuery
 * @package Servido\Repo
 */
abstract class AbstractQuery
{
	/**
	 * @var PDO
	 */
	protected $pdo;
	
	/**
	 * @var PDOStatement
	 */
	protected $stmt;
	
	/**
	 * @var string
	 */
	protected $query;
	
	/**
	 * @var ValueToBind[]
	 */
	protected $values;
	
	/**
	 * AbstractRepo constructor.
	 * @param PDO $pdo
	 */
	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
		$this->query = '';
		$this->values = [];
	}
	
	/**
	 * @return string
	 */
	public function dumpQuery(): string
	{
		return $this->query;
	}
	
	/**
	 * @return bool
	 */
	public function execute(): bool
	{
		$this->stmt = $this->pdo->prepare($this->query);
		foreach ($this->values as $value)
			$this->stmt->bindValue($value->getName(), $value->getValue(), $value->getPdoTypeParam());
		return $this->stmt->execute();
	}
}