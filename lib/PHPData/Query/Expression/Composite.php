<?php

namespace PHPData\Query\Expression;

class Composite implements \Countable {
	const TYPE_OR = "OR";
	const TYPE_AND = "AND";
	
	protected $type = array();
	protected $parts = array();
	
	public function __construct($type = self::TYPE_AND, array $parts = array())
	{
		$this->type = $type;
		$this->add($parts);
	}
	
	public function setType($type)
	{
		$this->type = $type;
	}
	
	public function getType()
	{
		return $this->type;
	}
	
	public function add($part)
	{
		if(func_num_args() > 1)
			$part = func_get_args();
			
		if(is_array($part))
			$this->parts = array_merge($this->parts, $part);
		else
			$this->parts[] = $part;
		
		return $this;
	}
	
	public function __toString()
	{
		if(count($this->parts) === 0)
			return "";
			
		if(count($this->parts) === 1)
		{
			return (string) $this->parts[0];
		}
		
		return '(' . implode(') ' . $this->type . ' (', $this->parts) . ')';
	}
	
	public function count()
	{
		return count($this->parts);
	}
}
