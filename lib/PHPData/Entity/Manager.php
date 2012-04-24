<?php

namespace PHPData\Entity;

class Manager {
	protected $driver = null;
	protected $connection = null;
	protected $schema = null;
	
	protected $entitiesNamespace = 'Entities\\';
	
	public function __construct($name, $parameters, \PHPData\Driver\Driver $driver = null)
	{
		Handler::register($name, $this);
		
		if(is_null($driver))
			$this->driver = \PHPData\Driver\Handler::getDriver($parameters['driver']);
		else
			$this->driver = $driver;
		
		$parameters['driver'] = $this->driver->getName();
		$this->connection = \PHPData\Driver\Handler::getConnection($parameters);
		$this->schema = new \PHPData\Schema($this->connection);
	}
	
	public function getDriver()
	{
		return $this->driver;
	}
	
	public function setEntitiesNamespace($entitiesNamespace)
	{
		$this->entitiesNamespace = $entitiesNamespace;
		return $this;
	}
	
	public function getConnection()
	{
		return $this->connection;
	}
	
	public function getSchema()
	{
		return $this->schema;
	}
	
	public function createQueryBuilder()
	{
		return new \PHPData\Query\Builder($this);
	}
	
	public function getEntityNameFromTable($table)
	{
		$name = "";
		foreach(explode('_', $table) as $part)
		{
			$name .= ucfirst($part);
		}
		return $name;
	}
	
	public function getTableFromEntityName($entityName)
	{
		return strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $entityName));
	}
	
	public function constructEntity($name, $data = array())
	{
		$className = $this->entitiesNamespace . $name;
		return new $className($data);
	}
}
