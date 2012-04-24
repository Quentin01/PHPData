<?php

namespace PHPData\Entity;

class Handler {
	protected static $entityManagers = array();
	
	protected function __construct() {}
	
	public static function get($name)
	{
		if(isset(self::$entityManagers[$name]))
			return self::$entityManagers[$name];
		else
			return false;
	}
	
	public static function getFromEntityName($name)
	{
		foreach(self::$entityManagers as $entityManager)
		{
			 if(array_search($entityManager->getTableFromEntityName($name), $entityManager->getSchema()->getTableNames()) !== false)
				return $entityManager;
		}
		
		return false;
	}
	
	public static function register($name, $entityManager)
	{
		self::$entityManagers[$name] = $entityManager;
	}
}
