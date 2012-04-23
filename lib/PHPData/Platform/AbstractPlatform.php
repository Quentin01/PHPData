<?php

namespace PHPData\Platform;

abstract class AbstractPlatform implements Platform {
	public function supportLimit() { return true; }
	public function supportOffset() { return true; }
	
	public function limit($limit, $offset = null)
	{
		return 'LIMIT ' . (int) $limit . ((!is_null($offset)) ? ' OFFSET ' . abs((int) $offset) : '');
	}
}
