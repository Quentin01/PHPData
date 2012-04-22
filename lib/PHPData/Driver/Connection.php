<?php

namespace PHPData\Driver;

interface Connection {
	function prepare($statement);
    function query();
    function quote($value, $type = null);
    function exec($statement);
    function lastInsertId($name = null);
    function beginTransaction();
    function commit();
    function rollBack();
    function errorCode();
    function errorInfo();
}
