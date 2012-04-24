<?php

namespace PHPData;

class Statement {
	protected $query = null;
	protected $statement = null;
	
	public function __construct(Query $query)
	{
		$this->query = $query;
		$this->statement = $this->query->getEntityManager()->getConnection()->prepare($this->query->getSQL());
	}
	
	public function execute()
	{
		$this->statement->execute();
	}
	
	public function bindParam($column, &$value, $type = null)
	{
		$this->statement->bindParam($column, $value, $type);
	}
	
	public function fetchAll($type = \PDO::FETCH_CLASS)
	{
		return new Collection($this, $type);
	}
	
	public function fetch($type = \PDO::FETCH_CLASS)
	{
		if($type !== \PDO::FETCH_CLASS)
			return $this->statement->fetch($type);
		
		$data = $this->statement->fetch(\PDO::FETCH_ASSOC);
		$queryData = $this->query->getData();
		
		if($data === false)
			return false;
		
		if(count($queryData) > 1)
		{
			foreach($queryData as $entity => $dataEntity)
			{
				$queryData[$entity] = $this->constructEntity($entity, $data, $dataEntity);
			}
			
			return $queryData;
		}
		else
		{
			$entityName = array_keys($queryData);
			$entityName = $entityName[0];
			
			return $this->constructEntity($entityName, $data, $queryData[$entityName]);
		}
	}
	
	protected function constructEntity($name, $data, $dataEntity)
	{
		foreach($dataEntity as $field => $fieldName)
		{
			if(is_array($fieldName))
			{
				$dataEntity[strtolower($field)] = $this->constructEntity($field, $data, $fieldName);
				unset($dataEntity[$field]);
			}
			else
			{
				$dataEntity[strtolower($fieldName)] = $data[$field];
				unset($dataEntity[$field]);
			}
		}
		
		return $this->query->getEntityManager()->constructEntity($name, $dataEntity);
	}
	
	public function __call($name, $args)
	{
		$method = new \ReflectionMethod($this->statement, $name);
		$method->invokeArgs($this->statement, $args);
	}
}
