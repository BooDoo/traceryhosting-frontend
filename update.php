<?php

header('Content-Type: application/json');

require_once("MastodonOAuthPHP/autoload.php");
require "credentials.php";

session_set_cookie_params(2678000);
session_start();

$instance_domain = $_SESSION['instance_domain'];

$pdo = new PDO('mysql:dbname=traceryhosting;host=127.0.0.1;charset=utf8mb4', 'tracery_php', DB_PASSWORD, array(
    PDO::MYSQL_ATTR_FOUND_ROWS => true
));

$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_SESSION['bearer_token']))
{
	try
	{
		//todo validate json here
		//TODO use url instead of token? tokens may have collision
		$stmt = $pdo->prepare('UPDATE traceries SET frequency=:frequency, tracery=:tracery, public_source=:public_source, is_sensitive=:is_sensitive, does_replies=:does_replies, reply_rules=:reply_rules, last_updated=now() WHERE bearer=:bearer');

	  	$stmt->execute(array('frequency' => $_POST['frequency'], 'tracery' => $_POST['tracery'],'public_source' => $_POST['public_source'], 'is_sensitive' => $_POST['is_sensitive'], 'does_replies' => $_POST['does_replies'],'reply_rules' => $_POST['reply_rules'], 'bearer' => $_SESSION['bearer_token']));

	  	if ($stmt->rowCount() == 1)
	  	{
	  		die ("{\"success\": true}");
	  	}
	  	else
	  	{
			die ("{\"success\": false, \"reason\" : \"row count mismatch\"}");
	  	}

	}
	catch(PDOException $e)
	{
		
		error_log($e);
		die ("{\"success\": false, \"reason\" : \"db err " . $e->getCode() . "\"}");
		//die($e); //todo clean this
	}

}
else
{
	die ("{\"success\": false, \"reason\" : \"Not signed in\"}");
}



?>
