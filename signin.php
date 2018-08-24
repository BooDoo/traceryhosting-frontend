<?php

require_once("MastodonOAuthPHP/autoload.php");
require "credentials.php";

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
$extra = 'callback.php';
define('OAUTH_CALLBACK', "$protocol$host$uri/$extra");

$pdo = new PDO('mysql:dbname=traceryhosting;host=127.0.0.1;charset=utf8mb4', 'tracery_php', DB_PASSWORD);

$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

parse_str( $_SERVER['QUERY_STRING'], $get_params);
$instance_domain = $get_params['instance_domain'];

$query = $pdo->prepare("SELECT client_id,client_secret,domain FROM instances WHERE domain='".$instance_domain."'");
$query->execute();
$res = $query->fetch();

if ($res == NULL) {
	echo("Please wait, generating app for {$instance_domain}...");
	# Kick off app creation here!!
	$res = create_new_app($instance_domain);
	sleep(5); # TODO: there has to be a better way!!
	header("Refresh:0");
}

# Proceed with OAuth outreach to specified Mastodon instance

if (!isset($res['client_id']) || !isset($res['client_secret']) || !isset($res['domain'])) {
	echo("Sorry, there was an issue creating/accessing an app on the\n\n\t {$instance_domain}\n\n Mastodon instance.");
	die();
}

	$client_id = $res['client_id'];
	$client_secret = $res['client_secret'];
	$domain = $res['domain'];

	$masto = new \theCodingCompany\Mastodon();
	$masto->setMastodonDomain($domain);
	$masto->setCredentials($res);
	$auth_url = $masto->getAuthUrl(OAUTH_CALLBACK);
	session_set_cookie_params(2678000);
	session_start();
	$_SESSION['instance_domain'] = $instance_domain;
	header("Location: {$auth_url}", true);

die();

function create_new_app($instance_domain)
{
	global $pdo;

	$connection = new \theCodingCompany\Mastodon($instance_domain);

	$token_info = $connection->createApp("CheapBotsTootSweet","https://cheapbotstootsweet.com","https://cheapbotstootsweet.com/callback.php");
	$token_info['instance_domain'] = $instance_domain;

	$stmt = $pdo->prepare('INSERT INTO instances (client_id,client_secret,domain) VALUES(:client_id, :client_secret, :domain)');
	$stmt->execute(array(
	   'client_id' => $token_info['client_id'],
	   'client_secret' => $token_info['client_secret'],
	   'domain' => $token_info['instance_domain'],
	));
	return $token_info;
}



?>
