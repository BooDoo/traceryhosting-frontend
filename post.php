<?php
header('Content-Type: application/json');

require "MastodonOAuthPHP/autoload.php";
require "credentials.php";

$pdo = new PDO('mysql:dbname=traceryhosting;host=127.0.0.1;charset=utf8mb4', 'tracery_php', DB_PASSWORD, array(
    PDO::MYSQL_ATTR_FOUND_ROWS => true
));

$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


session_set_cookie_params(2678000);
session_start();

if (isset($_SESSION['bearer_token']))
{
	try
	{

		$stmt = $pdo->prepare('SELECT * FROM traceries WHERE url = :url');

		$stmt->execute(array('url' => $_SESSION['url']));
		$result = $stmt->fetch(PDO::FETCH_ASSOC); 

		if ($result['blocked_status'] != 0) //are they blocked
		{
			switch ($result['blocked_status']) {
				case 1: //hellbanned
					die ("{\"success\": true}");
					break;
				
				default:
					die ("{\"success\": false, \"reason\" : \"This account has been blocked.\"}");
					break;
			}
		    	
		}

		$descriptorspec = array(
		   0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
		   1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
		   //2 => array("file", "/tmp/error-output.txt", "a") // stderr is a file to write to
		);

		$cwd = '/tmp';
		$env = array(	'ACCESS_TOKEN' => $result['bearer'],
				'INSTANCE_DOMAIN' =>  $result['instance'],
				'IS_SENSITIVE' => $result['is_sensitive'],
				'VISIBILITY' => $result['visibility']);


		$process = proc_open(NODE_PATH . " " . SENDSTATUS_PATH, $descriptorspec, $pipes, $cwd, $env);

		if (is_resource($process)) {
		    // $pipes now looks like this:
		    // 0 => writeable handle connected to child stdin
		    // 1 => readable handle connected to child stdout
		    // Any error output will be appended to /tmp/error-output.txt

		    fwrite($pipes[0], $_POST['status']);
		    fclose($pipes[0]);

		    $result = stream_get_contents($pipes[1]);
		    fclose($pipes[1]);

		    // It is important that you close any pipes before calling
		    // proc_close in order to avoid a deadlock
		    $return_value = proc_close($process);

		    if ($return_value === 0)
		    {
		    	die ("{\"success\": true}");
		    }
		    else
		    {
		    	die ("{\"success\": false, \"reason\" : " . json_encode($result) . "}");
		    }
		}
		else
		{
			die ("{\"success\": false, \"reason\" : \"can't find node\"}");
		}



	}
	catch(PDOException $e)
	{
		
		error_log($e);
		die ("{\"success\": false, \"reason\" : \"db err\"}");
		//die($e); //todo clean this
	}

}
else
{
	die ("{\"success\": false, \"reason\" : \"oauth failure\"}");
}



?>
