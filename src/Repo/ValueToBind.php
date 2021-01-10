<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 31/12/2020
 * Time: 22:38
 */

namespace Servido\Repo;

use PDO;

/**
 * Class ValueToBind
 * @package Servido\Repo
 */
class ValueToBind
{
	/**
	 * @var string
	 */
	private $name;
	
	/**
	 * @var mixed
	 */
	private $value;
	
	/**
	 * @var int
	 */
	private $pdoTypeParam;
	
	/**
	 * ValueToBind constructor.
	 * @param string $name
	 * @param mixed $value
	 * @param int $pdoTypeParam
	 */
	public function __construct(string $name, $value, int $pdoTypeParam = PDO::PARAM_STR)
	{
		$this->name = $name;
		$this->value = $value;
		$this->pdoTypeParam = $pdoTypeParam;
	}
	
	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}
	
	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}
	
	/**
	 * @return int
	 */
	public function getPdoTypeParam(): int
	{
		return $this->pdoTypeParam;
	}
	
	/**
	 * @return string
	 */
	public function toString(): string
	{
		return ':'.$this->name;
	}
}