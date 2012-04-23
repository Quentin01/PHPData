<?php

namespace PHPData\Driver\PDO\MySQL;

class Driver implements \PHPData\Driver\Driver {
	public function getConnection(array $parameters, $username = null, $password = null, array $options = array())
	{
		$dsn = 'mysql:';
        if(isset($parameters['host']) && !empty($parameters['host'])) {
            $dsn .= 'host=' . $parameters['host'] . ';';
        }
        if(isset($parameters['port'])) {
            $dsn .= 'port=' . $parameters['port'] . ';';
        }
        if(isset($parameters['dbname'])) {
            $dsn .= 'dbname=' . $parameters['dbname'] . ';';
        }
        if(isset($parameters['unix_socket'])) {
            $dsn .= 'unix_socket=' . $parameters['unix_socket'] . ';';
        }
        if(isset($parameters['charset'])) {
            $dsn .= 'charset=' . $parameters['charset'] . ';';
        }

		return $connnection = new \PHPData\Driver\PDO\Connection(
			$this,
            $dsn,
            $username,
            $password,
            $options
        );
	}
	
	public function getDatabasePlatform()
	{
		return new \PHPData\Platform\MySQL();
	}
	
	public function getName()
	{
		return "pdo_mysql";
	}
}
