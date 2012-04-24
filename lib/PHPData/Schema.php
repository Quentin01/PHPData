<?php

namespace PHPData;

class Schema {
	protected $connection = null;
	
	protected $allTableNames = false;
	protected $tablesData = array();
	
	public function __construct(\PHPData\Driver\Connection $connection)
	{
		$this->connection = $connection;
	}
	
	protected function loadAllTablesName()
	{
		$this->allTableNames = true;
		
		$statement = $this->connection->prepare('SHOW TABLES');
		$statement->execute();
		
		while($data = $statement->fetch())
		{
			$this->tablesData[$data[0]] = array();
		}
		
		$statement->closeCursor();
	}
	
	protected function loadTablesData($table)
	{
		$statement = $this->connection->prepare('DESCRIBE `' . $table . '`');
		$statement->execute();
		
		while($data = $statement->fetch())
		{
			$this->tablesData[$table][] = $data;
		}
		
		$statement->closeCursor();
	}
	
	public function getColumns($table)
	{
		if(!isset($this->tablesData[$table]) || empty($this->tablesData[$table]))
			$this->loadTablesData($table);
			
		$columns = array();
		
		foreach($this->tablesData[$table] as $data)
		{
			$columns[] = $data['Field'];
		}
		
		return $columns;
	}
	
	public function getTableNames()
	{
		if(!$this->allTableNames)
			$this->loadAllTablesName();
			
		return array_keys($this->tablesData);
	}
}
