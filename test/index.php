<?php
function autoload($class)
{
	require_once '../lib/' . str_replace('\\', '/', $class) . '.php';
}
spl_autoload_register('autoload'); 

$parameters = array(
	'host' => 'localhost',
	'dbname' => 'test',
	'driver' => 'pdo_mysql',
	'user' => 'root',
	'password' => '',
);

/*$driver = \PHPData\Driver\Handler::getDriver('pdo_mysql');
echo $driver->getName();
* 
$connection = $driver->getConnection($parameters, 'root', '');*/

$connection = \PHPData\Driver\Handler::getConnection($parameters, 'root', '');
	
/*$statement = $connection->prepare('SELECT * FROM chat');
$statement->execute();*/

//echo var_dump($statement->fetch(PDO::FETCH_ASSOC));

$builder = new \PHPData\Query\Builder($connection);

$builder->select('u.username', 'u.mail')
        ->from('users', 'u')
        ->innerJoin('group', 'g', 'g.id = u.group')
        ->where('u.password = :password')
        ->andWhere('u.username = :username')
        ->orWhere('g.id = :idgroup')
        ->limit(5);


echo $builder->getSQL();
