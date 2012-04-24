<?php

namespace PHPData\Query;

class Builder {
	const SELECT = 1;
	const UPDATE = 2;
	const DELETE = 3;
	
	const STATE_DIRTY = 0;
	const STATE_CLEAN = 1;
	
	protected $entityManager = null;
	protected $expressionBuilder = null;
	
	protected $type = self::SELECT;
	protected $state = self::STATE_CLEAN;
	protected $sql = null;
	
	protected $parts = array(
		'select' => array(),
		'from' => array(),
		'set' => array(),
		'join' => array(),
		'where' => null,
		'groupBy' => array(),
		'having' => null,
		'orderBy' => array(),
		'limit' => null,
		'offset' => null
	);
	
	public function __construct(\PHPData\Entity\Manager $entityManager)
	{
		$this->entityManager = $entityManager;
		$this->expressionBuilder = new Expression\Builder();
	}
	
	public function expr()
	{
		return $this->getExpressionBuilder();
	}
	
	public function getExpressionBuilder()
	{
		return $this->expressionBuilder;
	}
	
	public function getSQL()
	{
		if(!is_null($this->sql) && $this->state === self::STATE_CLEAN)
			return $this->sql;
			
		foreach($this->parts['select'] as $key => $select)
		{
			if(strpos($select, '*') === false)
				continue;
			
			$select = explode('.', $select);
			
			if(count($select) === 1)
			{
				foreach($this->parts['from'] as $table => $alias)
				{
					$this->addAllSelectFor($table, $alias);
				}
				
				foreach($this->parts['join'] as $join)
				{
					$this->addAllSelectFor($join['table'], $join['alias']);
				}
				
				unset($this->parts['select'][$key]);
				break;
			}
			else
			{
				$table = $this->getTableFromAlias($select[0]);
				
				if($table === false)
					$this->addAllSelectFor($select[0]);
				else
					$this->addAllSelectFor($table, $select[0]);
				
				unset($this->parts['select'][$key]);
			}
		}
		
		switch($this->type)
		{
			case self::UPDATE:
				$this->sql = $this->getUpdateSQL();
				break;
			case self::DELETE:
				$this->sql = $this->getDeleteSQL();
				break;
			case self::SELECT:
			default:
				$this->sql = $this->getSelectSQL();
				break;
		}
		
		$this->state = self::STATE_CLEAN;
		return $this->sql;
	}
	
	public function addAllSelectFor($table, $alias = null)
	{
		foreach($this->entityManager->getSchema()->getColumns($table) as $column)
		{
			if(is_null($alias))
				$this->add('select', $table . '.' . $column, true);
			else
				$this->add('select', $alias . '.' . $column, true);
		}
	}
	
	protected function getSelectSQL()
	{
		$query = 'SELECT ';
		
		foreach($this->parts['select'] as $field)
		{
			$query .= $field . ((strpos($field, '*') === false) ? ' AS \'' . $field . '\'' : '' ) . ', ';
		}
		
		$query = substr($query, 0, -2) . ' FROM ';
		
		foreach($this->parts['from'] as $table => $alias)
		{
			$query .= '`' . $table . '`' . ((!is_null($alias)) ? ' AS ' . $alias : '') . ', ';
		}
		
		$query = substr($query, 0, -2);
		
		foreach($this->parts['join'] as $join)
		{
			$query .= ' ' . strtoupper($join['type']) 
				. ' JOIN ' . '`' . $join['table'] . '`'. ((!is_null($join['alias'])) ? ' AS ' . $join['alias'] : '')
				. ' ON ' . (string) $join['on'];
		}
		
		$query .= ((!is_null($this->parts['where'])) ? ' WHERE ' . ((string) $this->parts['where']) : '')
				. ((!empty($this->parts['groupBy'])) ? ' GROUP BY ' . implode(', ', $this->parts['groupBy']) : '')
				. ((!is_null($this->parts['having'])) ? ' HAVING ' . ((string) $this->parts['having']) : '')
				. ((!empty($this->parts['orderBy'])) ? ' ORDER BY ' . implode(', ', $this->parts['orderBy']) : '')
				. ((!is_null($this->parts['limit'])) ? ' ' . $this->entityManager->getDriver()->getDatabasePlatform()->limit($this->parts['limit'], $this->parts['offset']) : '');
				
		return $query;
	}
	
	protected function getUpdateSQL()
	{
		return 'UPDATE ' . $this->parts[0]['from']['table'] . ($this->parts[0]['from']['alias'] ? ' AS ' . $this->parts[0]['from']['alias'] : '')
			. ' SET ' . implode(', ', $this->parts['set'])
			. ((!is_null($this->parts['where'])) ? ' WHERE ' . ((string) $this->parts['where']) : '');
	}
	
	protected function getDeleteSQL()
	{
		return 'DELETE FROM ' . $this->parts[0]['from']['table'] . ($this->parts[0]['from']['alias'] ? ' AS ' . $this->parts[0]['from']['alias'] : '') 
			. ((!is_null($this->parts['where'])) ? ' WHERE ' . ((string) $this->parts['where']) : '');
	}
	
	protected function add($name, $parts, $append = false)
	{
		$this->state = self::STATE_DIRTY;
		
		if(is_array($this->parts[$name]))
		{
			if($append)
				$parts = array($parts);
				
			$this->parts[$name] = array_unique(array_merge($this->parts[$name], $parts));
		}
		else
		{
			$this->parts[$name] = $parts;
		}
		
		return $this;
	}
	
	public function select($select = null)
	{
		$this->type = self::SELECT;
		
		if(is_null($select))
			return $this;
			
		$args = func_get_args();
		
		if(is_array($select))
			return $this->add('select', $select);
		else
			return $this->add('select', $args);
	}
	
	public function delete($from, $alias = null)
	{
		$this->type = self::DELETE;
		return $this->from($from, $alias);
	}
	
	public function update($from, $alias = null)
	{
		$this->type = self::UPDATE;
		return $this->from($from, $alias);
	}
	
	public function set($key, $value)
	{
		return $this->add('set', $key . ' = ' . $value, true);
	}
	
	public function from($table, $alias = null)
	{
		if(is_null($alias))
			$this->add('select', $table . '.id', true);
		else
			$this->add('select', $alias . '.id', true);
			
		return $this->add('from', array(
			$table => $alias,
		));
	}
	
	public function join($from, $table, $alias = null, $condition = null)
	{
		return $this->innerJoin($from, $table, $alias, $condition);
	}
	
	public function innerJoin($from, $table, $alias = null, $on = null)
	{
		if(is_null($alias))
			$this->add('select', $table . '.id', true);
		else
			$this->add('select', $alias . '.id', true);
			
		return $this->add('join', array(
			'from' => $from,
			'type' => 'inner',
			'table' => $table,
			'alias' => $alias,
			'on' => $on,
		), true);
	}
	
	public function leftJoin($from, $table, $alias = null, $on = null)
	{
		if(is_null($alias))
			$this->add('select', $table . '.id', true);
		else
			$this->add('select', $alias . '.id', true);
			
		return $this->add('join', array(
			'from' => $from,
			'type' => 'left',
			'table' => $table,
			'alias' => $alias,
			'on' => $on,
		), true);
	}
	
	public function rightJoin($from, $table, $alias = null, $on = null)
	{
		if(is_null($alias))
			$this->add('select', $table . '.id', true);
		else
			$this->add('select', $alias . '.id', true);
			
		return $this->add('join', array(
			'from' => $from,
			'type' => 'right',
			'table' => $table,
			'alias' => $alias,
			'on' => $on,
		), true);
	}
	
	public function where($predicates)
	{
		if(func_num_args() !== 1)
			$predicates = new Expression\Composite(Expression\Composite::TYPE_AND, func_get_args());
		
		return $this->add('where', $predicates);
	}
	
	public function andWhere()
	{
		$args = func_get_args();
		$where = $this->parts['where'];
		
		if($where instanceof Expression\Composite && $where->getType() === Expression\Composite::TYPE_AND)
			$where->add($args);
		else
		{
			$args[] = $where;
			$where = new Expression\Composite(Expression\Composite::TYPE_AND, $args);
		}
		
		return $this->add('where', $where);
	}
	
	public function orWhere()
	{
		$args = func_get_args();
		$where = $this->parts['where'];
		
		if($where instanceof Expression\Composite && $where->getType() === Expression\Composite::TYPE_OR)
			$where->add($args);
		else
		{
			$args[] = $where;
			$where = new Expression\Composite(Expression\Composite::TYPE_OR, $args);
		}
		
		return $this->add('where', $where);
	}
	
	public function having($predicates)
	{
		if(func_num_args() !== 1)
			$predicates = new Expression\Composite(Expression\Composite::TYPE_AND, func_get_args());
		
		return $this->add('having', $predicates);
	}
	
	public function andHaving()
	{
		$args = func_get_args();
		$having = $this->parts['having'];
		
		if($having instanceof Expression\Composite && $having->getType() === Expression\Composite::TYPE_AND)
			$having->add($args);
		else
		{
			$args[] = $having;
			$having = new Expression\Composite(Expression\Composite::TYPE_AND, $args);
		}
		
		return $this->add('having', $having);
	}
	
	public function orHaving()
	{
		$args = func_get_args();
		$having = $this->parts['having'];
		
		if($having instanceof Expression\Composite && $having->getType() === Expression\Composite::TYPE_OR)
			$having->add($args);
		else
		{
			$args[] = $having;
			$having = new Expression\Composite(Expression\Composite::TYPE_OR, $args);
		}
		
		return $this->add('having', $having);
	}
	
	public function groupBy()
	{
		$args = func_get_args();
		if(func_num_args() === 1 && is_array($args[0]))
			return $this->add('groupBy', $args[0]);
		else
			return $this->add('groupBy', $args);
	}
	
	public function orderBy()
	{
		$args = func_get_args();
		if(func_num_args() === 1 && is_array($args[0]))
			return $this->add('orderBy', $args[0]);
		else
			return $this->add('orderBy', $args);
	}
	
	public function limit($limit, $offset = null)
	{
		if(!$this->entityManager->getDriver()->getDatabasePlatform()->supportLimit())
			return false;
			
		if(!is_null($offset))
		{
			if(!$this->entityManager->getDriver()->getDatabasePlatform()->supportOffset())
				return false;
				
			$this->add('offset', $offset);
		}
			
		return $this->add('limit', $limit);
	}
	
	public function offset($offset)
	{
		if(!$this->entityManager->getDriver()->getDatabasePlatform()->supportOffset())
			return false;
			
		return $this->add('offset', $offset);
	}
	
	public function getParts($name = null)
	{
		if(is_null($name))
			return $this->parts;
		else
			return $this->parts[$name];
	}
	
	public function query()
	{
		if($this->state === self::STATE_DIRTY)
			$this->getSQL();
			
		return new \PHPData\Query($this->entityManager, $this);
	}
	
	public function getTableFromAlias($alias)
	{
		$table = array_search($alias, $this->parts['from']);
		
		if($table !== false)
			return $table;
			
		 foreach($this->parts['join'] as $join)
		 {
			 if(!is_null($join['alias']))
			 {
				if(strtolower($alias) === strtolower($join['alias']))
					return $join['table'];
			 }
		 }
		 
		 return false;
	}
}
