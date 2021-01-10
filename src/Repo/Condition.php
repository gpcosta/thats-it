<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 31/12/2020
 * Time: 19:57
 */

namespace Servido\Repo;

/**
 * Class Condition
 * @package Servido\Repo
 */
class Condition
{
	public const OPERATOR_EQUAL = '=';
	public const OPERATOR_DIFFERENT = '<>';
	public const OPERATOR_LIKE = 'LIKE';
	
	/**
	 * @var string
	 */
	private $operator;
	
	/**
	 * @var string
	 */
	private $leftSide;
	
	/**
	 * @var ValueToBind
	 */
	private $rightSide;
	
	/**
	 * Condition constructor.
	 * @param string $leftSide
	 * @param string $operator
	 * @param ValueToBind $rightSide
	 */
	public function __construct(string $leftSide, string $operator, ValueToBind $rightSide)
	{
		$this->leftSide = $leftSide;
		$this->operator = $operator;
		$this->rightSide = $rightSide;
	}
	
	/**
	 * @return ValueToBind
	 */
	public function getValue(): ValueToBind
	{
		return $this->rightSide;
	}
	
	/**
	 * @return string
	 */
	public function toString(): string
	{
		return $this->leftSide.$this->operator.$this->rightSide->toString();
	}
}