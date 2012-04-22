<?php

namespace PHPData\Driver;

class Handler {
	protected static $drivers = array(
		'pdo_mysql' => '\PHPData\Driver\PDO\MySQL\Driver',
	);
	
	protected function __construct() {}
	
	protected static function getDriverClass($name)
	{
		$name = strtolower($name);
		
		if(!isset(static::$drivers[$name]))
			return false;
			
		return static::$drivers[$name];
	}
	
	public static function getDriver($name = 'pdo_mysql')
	{
		if(($className = static::getDriverClass($name)) === false)
			return false;
		
		return new $className();
	}
	
	public static function getConnection(array $parameters, $username = null, $password = null, array $options = array())
	{
		if(!isset($parameters['driver']))
			return false;
			
		if(($driver = static::getDriver($parameters['driver'])) === false)
			return false;
		
		if(is_null($username) && isset($parameters['user'])) $username = $parameters['user'];
		if(is_null($username) && isset($parameters['username'])) $username = $parameters['username'];
		if(is_null($password) && isset($parameters['password'])) $password = $parameters['password'];
		
		return $driver->getConnection($parameters, $username, $password, $options);
	}
}
