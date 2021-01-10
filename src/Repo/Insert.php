<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 31/12/2020
 * Time: 22:27
 */

namespace ThatsIt\Repo;

/**
 * Class Insert
 * @package Servido\Repo
 */
class Insert extends AbstractQuery
{
	/**
	 * @param string $table
	 * @param Field[] $fields
	 * @return Insert
	 */
	public function insert(string $table, array $fields): self
	{
		$query = '';
		foreach ($fields as $field)
			$query .= $field->toString().', ';
		$query = substr($query, 0, -2);
		$this->query = 'INSERT INTO '.$table.'('.$query.')';
		return $this;
	}
	
	/**
	 * @param ValueToBind[] $values
	 * @return Insert
	 */
	public function values(array $values): self
	{
		$this->query .= ' VALUES (';
		foreach ($values as $value) {
			$this->values[] = $value;
			$this->query .= $value->toString().', ';
		}
		$this->query = substr($this->query, 0, -2).')';
		return $this;
	}
}