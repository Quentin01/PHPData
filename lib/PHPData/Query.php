<?php

namespace PHPData;

class Query {
	protected $entityManager = null;
	protected $queryBuilder = null;
	
	protected $parameters = array();
	
	public function __construct(Entity\Manager $entityManager, \PHPData\Query\Builder $queryBuilder)
	{
		$this->entityManager = $entityManager;
		$this->queryBuilder = $queryBuilder;
	}
	
	public function setParameter($name, $value, $type = null)
	{
		$this->parameters[$name] = array(
			'value' => $value,
			'type' => $type
		);
		return $this;
	}
	
	public function getEntityManager()
	{
		return $this->entityManager;
	}
	
	public function &getParameter($name)
	{
		if(isset($this->parameters[$name]))
			return $this->parameters[$name];
		else
			return false;
	}
	
	public function getSQL()
	{
		return $this->queryBuilder->getSQL();
	}
	
	public function execute()
	{
		$statement = new Statement($this);
		
		foreach($this->parameters as $name => $parameter)
		{
			$statement->bindParam($name, $this->parameters[$name]['value'], $parameter['type']);
		}
		
		$statement->execute();
		return $statement;
	}
	
	public function getData()
	{
		$data = array();
		$refEntities = array();
		
		foreach($this->queryBuilder->getParts('from') as $from => $alias)
		{
			$entity = $this->entityManager->getEntityNameFromTable($from);
			$data[$entity] = array();
			
			$refEntities[$entity] = &$data[$entity];
		}
		
		foreach($this->queryBuilder->getParts('join') as $join)
		{
			if($this->queryBuilder->getTableFromAlias($join['from']) !== false)
				$entity = $this->queryBuilder->getTableFromAlias($join['from']);
			else
				$entity = $join['from'];
			
			$entity = $this->entityManager->getEntityNameFromTable($entity);
			
			if(isset($data[$entity]))
			{
				$name = $this->entityManager->getEntityNameFromTable($join['table']);
				$data[$entity][$name] = array();
				$refEntities[$name] = &$data[$entity][$name];
			}
		}
		
		foreach($this->queryBuilder->getParts('select') as $field)
		{
			$fieldData = explode('.', $field);
			
			if(count($fieldData) === 1)
			{
				$fieldName = $fieldData[0];
				
				$entities = array_keys($data);
				$entity = $entities[0];
			}
			else
			{
				$fieldName = $fieldData[1];
				
				if($this->queryBuilder->getTableFromAlias($fieldData[0]) !== false)
					$entity = $this->queryBuilder->getTableFromAlias($fieldData[0]);

				$entity = $this->entityManager->getEntityNameFromTable($entity);
			}
			
			$refEntities[$entity][$field] = $fieldName;
		}
		
		return $data;
	}
}
