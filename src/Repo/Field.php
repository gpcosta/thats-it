<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 31/12/2020
 * Time: 20:32
 */

namespace Servido\Repo;

/**
 * Class Field
 * @package Servido\Repo
 */
class Field
{
	/**
	 * @var string
	 */
	private $name;
	
	/**
	 * @var string
	 */
	private $tableName;
	
	/**
	 * @var string
	 */
	private $function;
	
	/**
	 * @var string
	 */
	private $alias;
	
	/**
	 * Field constructor.
	 * @param string $name
	 * @param string $tableName
	 * @param string $function
	 * @param string $alias
	 */
	public function __construct(string $name, string $tableName = '', string $function = '', string $alias = '')
	{
		$this->name = $name;
		$this->tableName = $tableName;
		$this->function = $function;
		$this->alias = $alias;
	}
	
	/**
	 * @return string
	 */
	public function toString(): string
	{
		$result = $this->name;
		if ($this->tableName)
			$result = $this->tableName.'.'.$this->name;
		if ($this->function)
			$result = $this->function.'('.$this->name.')';
		if ($this->alias)
			$result .= ' AS '.$this->alias;
		return $result;
	}
}