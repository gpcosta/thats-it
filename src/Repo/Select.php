<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 31/12/2020
 * Time: 19:53
 */

namespace ThatsIt\Repo;

use PDO;
use ThatsIt\Exception\PlatformException;

/**
 * Class Select
 * @package Servido\Repo
 */
class Select extends AbstractQuery
{
	public const JOIN_TYPE_INNER_JOIN = 'JOIN';
	public const JOIN_TYPE_LEFT_JOIN = 'LEFT JOIN';
	public const JOIN_TYPE_RIGHT_JOIN = 'RIGHT JOIN';
	public const JOIN_TYPE_FULL_JOIN = 'FULL JOIN';
	
	/**
	 * @param Field[] $fields
	 * @return Select
	 */
	public function select(array $fields = []): self
	{
		if (count($fields) == 0)
			$fields = ['*'];
		
		$query = '';
		foreach ($fields as $field)
			$query .= $field->toString().', ';
		$query = substr($query, 0, -2);
		$this->query = 'SELECT '.$query;
		return $this;
	}
	
	/**
	 * @param string $fromTable
	 * @param string $alias
	 * @return Select
	 */
	public function from(string $fromTable, string $alias): self
	{
		$this->query .= ' FROM '.$fromTable.($alias ? ' '.$alias : '');
		return $this;
	}
	
	/**
	 * @param string $joinType
	 * @param string $joinTable
	 * @param string $joinAlias
	 * @param Condition[] $conditions
	 * @return Select
	 */
	public function join(string $joinType, string $joinTable, string $joinAlias = '', array $conditions = []): self
	{
		if (count($conditions) == 0)
			return $this;
		
		$this->query .= ' '.$joinType.' '.$joinTable.($joinAlias ? ' '.$joinAlias : '').' ON ';
		foreach ($conditions as $condition) {
			$this->values[] = $condition->getValue();
			$this->query .= $condition->toString().', ';
		}
		$this->query = substr($this->query, 0, -2);
		return $this;
	}
	
	/**
	 * @param Condition[] $conditions
	 * @return Select
	 */
	public function where(array $conditions): self
	{
		if (count($conditions) == 0)
			return $this;
		
		$this->query .= ' WHERE ';
		foreach ($conditions as $condition) {
			$this->values[] = $condition->getValue();
			$this->query .= $condition->toString().', ';
		}
		$this->query = substr($this->query, 0, -2);
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getNextRow()
	{
		return $this->stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	/**
	 * @param string $objectClass
	 * @return Object with $objectClass as class
	 * @throws PlatformException
	 */
	public function getNextObject(string $objectClass)
	{
		if (!array_key_exists(AbstractObject::class, class_parents($objectClass)))
			throw new PlatformException(
				'Invalid objectClass because objectClass must extend '.AbstractObject::class, 500
			);
		
		$row = $this->getNextRow();
		if ($row === null)
			return null;
		return $objectClass::getInstanceBasedInRow($this->pdo, $row);
	}
	
	/**
	 * @param string $objectClass
	 * @return Object[] with $objectClass as class
	 * @throws PlatformException
	 */
	public function getAllObjects(string $objectClass): array
	{
		$result = [];
		while ($object = $this->getNextObject($objectClass))
			$result[] = $object;
		return $result;
	}
}