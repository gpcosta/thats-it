<?php
/**
 * Created by PhpStorm.
 * User: Costa
 * Date: 31/12/2020
 * Time: 23:09
 */

namespace ThatsIt\Repo;

use PDO;
use ThatsIt\Exception\PlatformException;

/**
 * Class AbstractObject
 * @package ThatsIt\Repo
 */
abstract class AbstractObject
{
	/**
	 * @var array
	 */
	private static $populatedFields;
	
	/**
	 * @param string[] $populatedFields - fields that will be populated following the call of this function
	 */
	public static function setPopulatedFields(array $populatedFields): void
	{
		self::$populatedFields = [];
		
		$objectFields = self::getObjectFieldsNames();
		foreach ($populatedFields as $field) {
			if (array_key_exists($field, $objectFields))
				self::$populatedFields[] = $field;
		}
	}
	
	protected static function getObjectField(string $field, string $tableName = '', string $function = '',
											  string $alias = ''): Field
	{
		$fieldsInDB = self::getObjectFieldsNamesInDB();
		if (!in_array($field, $fieldsInDB))
			throw new PlatformException('There is no such field.', 500);
		return new Field($fieldsInDB[$field], $tableName, $function, $alias);
	}
	
	/**
	 * @return string[]
	 */
	abstract public static function getObjectFieldsNames(): array;
	
	/**
	 * @return array[
	 * 		name of field on object (string) => name of field in DB (string)
	 * ]
	 */
	abstract public static function getObjectFieldsNamesInDB(): array;
	
	/**
	 * @param PDO $pdo
	 * @param array $row
	 * @return mixed
	 */
	abstract public function getInstanceBasedInRow(PDO $pdo, array $row);
}