<?php
require  'libraries/Medoo.php';
use Medoo\Medoo;
 
$database = new Medoo([
	'database_type' => 'mysql',
	'database_name' => 'ipt_api',
	'server' => 'localhost',
	'username' => 'root',
	'password' => '',
]);

define('SECRET_KEY','Super-Secret-Key');  // secret key can be a random string and keep in secret from anyone
define('ALGORITHM','HS256');   // Algorithm used to sign the token

?>