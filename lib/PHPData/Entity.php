<?php

namespace PHPData;

class Entity {
	protected static $entityManager = array();
	protected static $columns = array();
	
	protected $_data = array();
	
	public function __construct($data = array())
	{
		$this->_data = $data;
	}
	
	public function __set($name, $value)
	{
		$name = strtolower($name);
		
		if(array_search($name, $this->getColumns()) !== false)
		{
			$this->_data[$name] = $value;
		}
		else
		{
			$id = explode('id', $name);
		
			if(empty($id[0]) && !empty($id[1]))
			{
				if(isset($this->_data[$id[1]]) && !is_object($this->_data[$id[1]]))
					$this->_data[$id[1]] = $value;
				elseif(isset($this->_data[$id[1]]) && is_object($this->_data[$id[1]]))
					$this->_data[$id[1]]->id = $value;
				else
					$this->_data[$id[1]] = $value;
					
			}
		}
	}
	
	public function __get($name)
	{
		$name = strtolower($name);

		if(array_search($name, $this->getColumns()) !== false)
		{
			if(isset($this->_data[$name]))
			{
				/*
				 *  Verification de si on doit charger un objet ou non à faire ( relation entre entités )
				 */
				return $this->_data[$name];
			}
			else
			{
				$this->load();
				return $this->$name;
			}
		}
		else
		{
			$id = explode('id', $name);
		
			if(empty($id[0]) && !empty($id[1]))
			{
				if(isset($this->_data[$id[1]]) && !is_object($this->_data[$id[1]]))
					return $this->_data[$id[1]];
				elseif(isset($this->_data[$id[1]]) && is_object($this->_data[$id[1]]))
					return $this->_data[$id[1]]->id;
				else
				{
					$this->load();
					return $this->$name;
				}
			}
		}
		
		return false;
	}
	
	public function load()
	{
		
	}
	
	public function save()
	{
		
	}
	
	public static function getColumns()
	{
		$entityName = static::getName();
		
		if(!isset(static::$columns[$entityName]) || empty(static::$columns[$entityName]))
			return static::$columns[$entityName] = static::getEntityManager()->getSchema()->getColumns(static::getTable());
		else
			return static::$columns[$entityName];
	}
	
	public static function getEntityManager()
	{
		$entityName = static::getName();
		
		if(!isset(static::$entityManager[$entityName]) || empty(static::$entityManager[$entityName]))
			return static::$entityManager[$entityName] = \PHPData\Entity\Handler::getFromEntityName($entityName);
		else
			return static::$entityManager[$entityName];
	}
	
	public static function getTable()
	{
		return static::getEntityManager()->getTableFromEntityName(static::getName());
	}
	
	public static function getName()
	{
		$class = explode('\\', get_class(new static()));
		return $class[(count($class) - 1)];
	}
}
