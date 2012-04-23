<?php

namespace PHPData\Query\Expression;

class Builder {
	const EQ  = '=';
	const NEQ = '<>';
	const LT  = '<';
	const LTE = '<=';
	const GT  = '>';
	const GTE = '>=';
	
	public function __construct() { }
	
	public function compositeAnd()
	{
		return new Composite(Composite::TYPE_AND, func_get_args());
	}
	
	public function compositeOr()
	{
		return new Composite(Composite::TYPE_OR, func_get_args());
	}
	
	public function comparison($x, $operator, $y)
	{
		return $x . ' ' . $operator . ' ' . $y;
	}
	
	public function eq($x, $y)
	{
		return $this->comparison($x, self::EQ, $y);
	}
	
	public function neq($x, $y)
	{
		return $this->comparison($x, self::NEQ, $y);
	}
	
	public function lt($x, $y)
	{
		return $this->comparison($x, self::LT, $y);
	}
	
	public function lte($x, $y)
	{
		return $this->comparison($x, self::LTE, $y);
	}
	
	public function gt($x, $y)
	{
		return $this->comparison($x, self::GT, $y);
	}
	
	public function gte($x, $y)
	{
		return $this->comparison($x, self::GTE, $y);
	}
	
	public function isNull($x)
	{
		return $x . ' IS NULL';
	}
	
	public function isNotNull($x)
	{
		return $x . ' IS NOT NULL';
	}
	
	public function like($x, $y)
	{
		return $this->comparison($x, 'LIKE', $y);
	}
}
