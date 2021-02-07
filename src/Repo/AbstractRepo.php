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
 * Class AbstractRepo
 * @package ThatsIt\Repo
 */
abstract class AbstractRepo
{
	/**
	 * @var array
	 */
	protected static $populatedFields;
	
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
	
	/**
	 * @param string $field
	 * @param string $tableName
	 * @param string $function
	 * @param string $alias
	 * @return Field
	 * @throws PlatformException
	 */
	protected static function getObjectField(string $field, string $tableName = '', string $function = '',
											  string $alias = ''): Field
	{
		$fieldsInDB = self::getObjectFieldsNamesInDB();
		if (!in_array($field, $fieldsInDB))
			throw new PlatformException('There is no such field.', 500);
		return new Field($fieldsInDB[$field], $tableName, $function, $alias);
	}
	
	/**
	 * @param array $row
	 * @param string $field
	 * @param bool $hasDefaultValue
	 * @param null $defaultValue
	 * @param array $aliases
	 * @return mixed
	 * @throws PlatformException
	 */
	protected static function getFieldFromRow(array $row, string $field, bool $hasDefaultValue = false,
											   $defaultValue = null, array $aliases = [])
	{
		$fieldsNamesInDB = self::getObjectFieldsNamesInDB();
		if (!array_key_exists($field, $fieldsNamesInDB))
			throw new PlatformException('There is no such field.', 500);
		
		if ($hasDefaultValue)
			return array_key_exists(self::getAlias($fieldsNamesInDB[$fieldDBName], $aliases), $row) ?
				$row[self::getAlias($fieldsNamesInDB[$fieldDBName], $aliases)] : $defaultValue;
		else
			$row[self::getAlias($fieldsNamesInDB[$fieldDBName], $aliases)];
	}
	
	/**
	 * @param string $objectFieldName
	 * @return string
	 */
	protected static function getObjectFieldNameInDB(string $objectFieldName): string
	{
		return self::getObjectFieldsNames()[$objectFieldName];
	}
	
	/**
	 * @param string $objectFieldName
	 * @return array|string
	 */
	protected static function getObjectFieldInDB(string $objectFieldName)
	{
		return self::getObjectFieldsInDB()[$objectFieldName];
	}
	
	/**
	 * Get alias of the given $field. If there is no alias, the $field is returned
	 *
	 * @param string $field
	 * @param array $aliases
	 * @return string
	 */
	protected static function getAlias(string $field, array $aliases): string
	{
		if (array_key_exists($field, $aliases))
			return $aliases[$field];
		return $field;
	}
	
	/**
	 * @return string[]
	 */
	abstract protected static function getObjectFieldsNames(): array;
	
	/**
	 * @return array[
	 * 		name of field on object (string) => name of field in DB (string) or info about join (array)
	 * ]
	 */
	abstract protected static function getObjectFieldsInDB(): array;
	
	/**
	 * @param PDO $pdo
	 * @param Condition|string $where -
	 * @return mixed
	 */
	abstract public static function getQuery(PDO $pdo, array $where);
	
	/**
	 * @param PDO $pdo
	 * @param array $row
	 * @param array $aliases
	 * @return mixed
	 */
	abstract public static function getInstanceBasedInRow(PDO $pdo, array $row, array $aliases = []);
	
	/**
	 * @param PDO $pdo
	 * @return mixed
	 */
	abstract public static function getNullInstance(PDO $pdo);
}