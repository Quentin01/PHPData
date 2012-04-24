<?php
use \PHPData\Entity\Manager as EntityManager;

function autoload($class)
{
	if(file_exists($path = '../lib/' . str_replace('\\', '/', $class) . '.php'))
		require_once $path;
	elseif(file_exists($path = str_replace('\\', '/', $class) . '.php'))
		require_once $path;
}
spl_autoload_register('autoload'); 

$parameters = array(
	'host' => 'localhost',
	'dbname' => 'PHPData',
	'driver' => 'pdo_mysql',
	'user' => 'root',
	'password' => '',
);

$em = new EntityManager('default', $parameters);

$builder = $em->createQueryBuilder();

$builder->select('u.username', 'u.email', 'g.*')
        ->from('user', 'u')
        ->leftJoin('u', 'group', 'g', 'g.id = u.group')
        ->where('g.name = :namegroup')
        ->limit(5);


echo $builder->getSQL();

$query = $builder->query();
$statement = $query->setParameter(':namegroup', 'Admin')
                   ->execute();

$user = $statement->fetch();
$group = $user->group;

echo '<br/>' . $user->group->name;
