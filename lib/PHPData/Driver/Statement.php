<?php

namespace PHPData\Driver;

interface Statement {
	function bindValue($param, $value, $type = null);
	function bindParam($column, &$variable, $type = null);
	
	function rowCount();
	function columnCount();
	
	function setFetchMode($fetchStyle);
	
	function fetch($fetchStyle = \PDO::FETCH_ASSOC);
	function fetchAll($fetchStyle = \PDO::FETCH_ASSOC);
	function fetchColumn($columnIndex = 0);
	function execute($params = null);
	
	function closeCursor();
	
	function errorCode();
	function errorInfo();
}
