<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 31/12/2020
 * Time: 23:36
 */

namespace Servido\Repo;

/**
 * Class Cache
 * @package Servido\Repo
 */
class Cache
{
	/**
	 * @var array
	 */
	private $cache;
	
	/**
	 * Cache constructor.
	 */
	public function __construct()
	{
		$this->cache = [];
	}
	
	/**
	 * @param string $key
	 * @param mixed|null $default
	 * @return mixed|null
	 */
	public function get(string $key, $default = null)
	{
		if (!array_key_exists($key, $this->cache))
			return $default;
		
		return $this->cache[$key];
	}
	
	/**
	 * @param string $key
	 * @param $value
	 */
	public function set(string $key, $value): void
	{
		$this->cache[$key] = $value;
	}
	
	/**
	 * @param string $key
	 */
	public function delete(string $key): void
	{
		unset($this->cache[$key]);
	}
}