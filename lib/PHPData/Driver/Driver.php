<?php

namespace PHPData\Driver;

interface Driver {
	function getConnection(array $parameters, $username = null, $password = null, array $options = array());
	function getDatabasePlatform();
	function getName();
}
