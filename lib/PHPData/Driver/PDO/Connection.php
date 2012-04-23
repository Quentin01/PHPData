<?php

namespace PHPData\Driver\PDO;

class Connection extends \PDO implements \PHPData\Driver\Connection {
	protected $driver = null;
	
	public function __construct($driver, $dsn, $user = null, $password = null, array $options = null)
    {
        parent::__construct($dsn, $user, $password, $options);
        $this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array('PHPData\Driver\PDO\Statement', array()));
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        $this->driver = $driver;
    }
    
    public function getDriver()
    {
		return $this->driver;
	}
}
