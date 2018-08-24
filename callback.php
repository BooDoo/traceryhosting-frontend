<?php

require_once("MastodonOAuthPHP/autoload.php");
require "credentials.php";

session_set_cookie_params(2678000);
session_start();

$instance_domain = $_SESSION['instance_domain'];
parse_str( $_SERVER['QUERY_STRING'], $get_params);


$pdo = new PDO('mysql:dbname=traceryhosting;host=127.0.0.1;charset=utf8mb4', 'tracery_php', DB_PASSWORD);

$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->prepare("SELECT client_id,client_secret FROM instances WHERE domain=:instance_domain");
$stmt->execute(array(
	'instance_domain' => $instance_domain
));
$res = $stmt->fetch();

function login_failure()
{
  $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
    die('Error! Couldn\'t log in. <a href="/">Retry</a>');
}

if ( (!isset($_GET['code']) || !isset($res)) ) {
	// Abort! Something is wrong.
    echo("Can't get code {$_GET['code']} or empty db res.\n\n");
    login_failure();
}

$auth_code = $_GET['code'];	
$connection = new \theCodingCompany\Mastodon($instance_domain);
$connection->setCredentials(array(
	"client_id" => $res['client_id'],
	"client_secret" => $res['client_secret']
));
$bearer_token = $connection->getAccessToken($auth_code);

if ($bearer_token == false)
{
  echo("Failed to get bearer token from {$instance_domain}.\n\n");
  login_failure();
}

$connection->setCredentials(array("bearer"=>$bearer_token));
$user_data = $connection->getUser();
$stmt = $pdo->prepare('INSERT INTO traceries (bearer, username, instance, acct, id, url) VALUES(:bearer, :username, :instance, :acct, :id, :url) ON DUPLICATE KEY UPDATE bearer=:bearer2, username=:username2, instance=:instance2, acct=:acct2, id=:id2, url=:url2');

$stmt->execute(array(   'bearer' => $bearer_token, 
			'username' => $user_data["username"],
			'instance' => $instance_domain,
		        'acct' => $user_data["acct"], 
		        'id' => $user_data["id"],
		        'url' => $user_data["url"],
			'bearer2' => $bearer_token, 
			'username2' => $user_data["username"],
			'instance2' => $instance_domain,
		        'acct2' => $user_data["acct"], 
		        'id2' => $user_data["id"],
		        'url2' => $user_data["url"],
                    ));


$_SESSION['bearer_token'] = $bearer_token;
$_SESSION['acct'] = $user_data["acct"];
$_SESSION['profile_pic'] = $user_data["avatar_static"]; 
$_SESSION['username'] =  $user_data["username"]; 
$_SESSION['id'] = $user_data["id"];
$_SESSION['url'] = $user_data["url"];

if (!(isset($user_data) || !(isset($user_data['url']))))
{
	login_failure(); 
}
if (isset($_SERVER['HTTPS']) &&
	($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
	isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
	$_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
	$protocol = 'https://';
}
else {
	$protocol = 'http://';
	}
$host  = $_SERVER['HTTP_HOST'];
$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
header("Location: $protocol$host$uri");
die();

?>
