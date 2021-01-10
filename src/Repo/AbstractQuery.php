<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 31/12/2020
 * Time: 19:37
 */

namespace Servido\Repo;

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
	private $pdo;
	
	/**
	 * @var PDOStatement
	 */
	private $stmt;
	
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
	
	/**
	 * @return array
	 */
	public function getNextRow(): array
	{
		return $this->stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	/**
	 * @param string $objectClass
	 * @return Object with $objectClass as class
	 * @throws \Exception
	 */
	public function getNextObject(string $objectClass)
	{
		if (!array_key_exists(AbstractObject::class, class_parents($objectClass)))
			throw new \Exception('Invalid objectClass because objectClass must extend '.AbstractObject::class);
		
		return $objectClass::getInstanceBasedInRow($this->pdo, $this->getNextRow());
	}
}