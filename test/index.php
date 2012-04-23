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

$builder = new \PHPData\Query\Builder($connection);

$builder->select('c.pseudo', 'c.date')
        ->from('chat', 'c')
        ->where('c.pseudo = :pseudo')
        ->limit(5);


echo $builder->getSQL();

$statement = $connection->prepare($builder->getSQL());
$statement->execute(array(':pseudo' => 'Anonyme24'));

echo var_dump($statement->fetchAll(PDO::FETCH_ASSOC));
