<?php


require_once("MastodonOAuthPHP/autoload.php");
require "credentials.php";

if (isset($_SESSION["instance_domain"])) {
	$instance_domain = $_SESSION["instance_domain"];
}
else {
	$instance_domain = 'botsin.space';
}

$pdo = new PDO('mysql:dbname=traceryhosting;host=127.0.0.1;charset=utf8mb4', 'tracery_php', DB_PASSWORD);

$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


session_set_cookie_params(2678000);
session_start();

// Dynamically determine our protocol/host
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
define('APP_ROOT', "$protocol$host");


?>
<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang=""> <!--<![endif]-->
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title>Cheap Bots, Toot Sweet!</title>
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="apple-touch-icon" href="apple-touch-icon.png">

	<link rel="stylesheet" href="css/bootstrap.min.css">
		<style>
			body {
				/*padding-top: 50px;*/
				padding-bottom: 40px;
			}
		</style>
		<!--<link rel="stylesheet" href="css/bootstrap-theme.min.css">-->
		<link rel="stylesheet" href="css/main.css">
		<link href='//fonts.googleapis.com/css?family=Yesteryear' rel='stylesheet' type='text/css'>
		<script src="js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.11/lodash.min.js" async></script>
	</head>
	<body>
<?php
if (!isset($_SESSION['url']))
{
  ?>

	<div class="container-fluid">

		<h1 class="header text-center cursive">Cheap Bots, Toot Sweet!</h1>
		<br><br>
		<div class="row">
			<div class="col-md-6 col-md-offset-3">
			
		<p>This site will help you make a Mastobot! They're easy to make and free (for you) to run.
		</p>

			<p>To use this, create an account for your bot to use on a bot-friendly Mastodon instance (such as <a href="https://botsin.space/auth/sign_up">botsin.space</a>) then fill in your instance down below to get started. If you're having trouble with a particular instance, feel free to let me know at <a href="https://mastodon.social/@boodoo">@boodoo@m.s</a> or <a href="https://twitter.com/boodooperson">@boodooperson</a> on the birdsite.<br><br> 
			Bots are written in <a href="http://www.brightspiral.com">Tracery</a>, a tool for writing generative grammars developed by <a href="http://www.galaxykate.com/">Kate Compton</a>.<br>
					The original <a href="https://cheapbotsdonequick.com/">CheapBotsDoneQuick.com</a> was created by <a href="https://v21.io">V Buckenham</a> - they have <a href="https://www.patreon.com/v21">a Patreon</a>, I do not.</p>
			<br>
			<p>In lieu of helping pay for server costs, maintenance and further development, please consider
				<details id="charity_list"><summary><a>Supporting a charity</a></summary>
					<ul id="charities">
						<li><a href="https://barcc.org/join/donation">Boston Area Rape Crisis Center (BARCC)</a></li>
						<li><a href="https://bailproject.org/donate">The Bail Project</a></li>
						<li><a href="https://www.msf.org/donate">Médecins Sans Frontières (Doctors Without Borders)</a></li>
						<li><a href="https://abortionfunds.org/donate">National Network of Abortion Funds</a></li>
						<li><a href="https://www.transyouthequality.org/#block-yui_3_17_2_2_1447266818747_13555">Trans Youth Equality Foundation (TYEF)</li>
						<li><a href="https://vocal.ourpowerbase.net/civicrm/contribute/transact?id=6">VOCAL New York</a></li>
					</ul>
				</details>
			</p>
			</div>
		</div>
		
		<br>
	<div class="col-md-6 col-md-offset-3 form-inline">
		<div class="form-group">
			<p><strong>⚠ ONLY the instance domain! If using <a href="https://botsin.space" title="Bots in Space">botsin.space</a>, just click the button!</strong></p>
			<label for="instance-domain">https://</label>
			<input value="" placeholder="botsin.space" list="instance-domains" id="instance-domain" class="form-control" type="text">
			<datalist id="instance-domains">
			<option>botsin.space</option>
			<option>mastodon.social</option>
			</datalist>
			<a href="#" onclick="this.href='/signin.php?instance_domain='+(document.getElementById('instance-domain').value || 'botsin.space'); return true;">
				<button class="btn btn-default" id="signin-button">
					Continue with Mastodon
				</button>
			</a>
		</div>
	</div>
	<br><br>
		<div class="row">
			<div class="col-md-6 col-md-offset-3">
			<p><br>Some examples of Mastobots made with this site:</p>
		<ul id="shuffle">
		<!-- <h3 style=>NONE YET.</h3> -->
		<li><a href="https://botsin.space/@levels_check">@levels_check</a> <a href="<?=APP_ROOT ?>/source/?url=https://botsin.space/@levels_check">(source)</a></li>
		<li><a href="https://botsin.space/@bodega">@bodega</a> <a href="<?=APP_ROOT ?>/source/?url=https://botsin.space/@bodega">(source)</a></li>
		<li><a href="https://botsin.space/@bratsinspace">@bratsinspace</a> <a href="<?=APP_ROOT ?>/source/?url=https://botsin.space/@bratsinspace">(source)</a></li>
		<li><a href="https://botsin.space/@thetinygallery">@thetinygallery</a> <a href="<?=APP_ROOT ?>/source/?url=https://botsin.space/@thetinygallery">(source)</a></li>
		<li><a href="https://botsin.space/@EthanMarsDad">@EthanMarsDad</a> <a href="<?=APP_ROOT ?>/source/?url=https://botsin.space/@EthanMarsDad">(source)</a></li>
		<li><a href="https://botsin.space/@robotrecipes">@robotrecipes</a> <a href="<?=APP_ROOT ?>/source/?url=https://botsin.space/@robotrecipes">(source)</a></li>
		<li><a href="https://botsin.space/@undeadmerchant">@undeadmerchant</a> <a href="<?=APP_ROOT ?>/source/?url=https://botsin.space/@undeadmerchant">(source)</a></li>
			<!--<li><a href="https://botsin.space/@AbhorrentSexBot">@AbhorrentSexBot</a> <a href="<?=APP_ROOT ?>/source/?url=https://botsin.space/@AbhorrentSexBot">(source)</a></li>-->
			
			</ul>

<script type="text/javascript">
var ul = document.getElementById("shuffle");
for (var i = ul.children.length; i >= 0; i--) {
	ul.appendChild(ul.children[Math.random() * i | 0]);
}
</script>
			</div>
		</div>


<!--
		<hr>

		<footer>
		<p>&copy; Company 2015</p>
		</footer>-->
	</div> <!-- /container -->        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
		<script>window.jQuery || document.write('<script src="js/vendor/jquery-1.11.2.min.js"><\/script>')</script>

		<script src="js/vendor/bootstrap.min.js"></script>

	</body>
</html>


<?php
die();

}
//we've got an account
$stmt = $pdo->prepare('SELECT * FROM traceries WHERE url = :url');

$stmt->execute(array('url' => $_SESSION['url']));
$result = $stmt->fetch(PDO::FETCH_ASSOC); 

//todo handle failing to find user

//read from db

	
?>

	<div class="container-fluid">

	<h1 class="header text-center cursive">Cheap Bots, Toot Sweet!</h1>
		<br>
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
			<p>Bots are written in <a href="http://brightspiral.com/">Tracery</a>, a generative grammar specified as a <a href="http://www.tutorialspoint.com/json/json_syntax.htm">JSON</a> string. This site will automatically expand your text, starting from the "origin" node, and then post it on a fixed schedule. If it generates a duplicate status, or a status over 280 characters, it will retry up to 5 times. Line breaks can be entered with the special sequence <code>\n</code>, and hashtags with <code>\\#</code>.</p>

			<p>You can also include images in your stauses. The simplest way to do this is to specify a URL, like so:<br>
			<code>{img https://placeimg.com/640/480/animals/image.jpg}</code></p>

			<p>To generate images within CBTS, you can use <a href="https://developer.mozilla.org/en-US/docs/Web/SVG">SVGs</a>. SVGs will need to specify a <code>width</code> and <code>height</code> attribute, and should declare the appropriate namespaces. The format would look something like:<br>
			<code>{svg &lt;svg xmlns='http://www.w3.org/2000/svg' width='#svgWidth#' height='#svgHeight#'...&gt; ... &lt;/svg&gt;}</code><br>
			Within SVG, <code>"</code>s need to be escaped as <code>\"</code>, and <code>#</code>s as <code>\\#</code>. <code>{</code>s and <code>}</code>s are escaped as <code>\\\\\\\\{</code> and <code>\\\\}</code>, respectively.<br>
			A good simple example to start from is the source of <a href="//cheapbotsdonequick.com/source/hashfacade">@hashfacade</a>.</p>

			<p>We also offer some Mastodon-specific features:
			<ul>
				<li>Set visbility for your post with <code>{public}</code>, <code>{unlisted}</code>, <code>{private}</code>, or <code>{direct}</code></li>
				<li>Put your status behind a CW that reads <em>Food [+]</em> like this: <code>{cut Food \\[+\\]}</code></li>
				<li>Use <code>{alt a description of the image}</code> near an <code>{img…}</code> or <code>{svg…}</code> tag to assist folks using screen readers.
				<li>Use <code>{show}</code> anywhere to override your default and have your media shown for that post</li>
				<li>Use <code>{hide}</code> anywhere in your generated status to flag your media as sensitive (this overrules <code>{show}</code>)</li>
			</ul>


			<p>If you create a bot I, or people I trust, find abusive or otherwise unpleasant I reserve the right to terminate it. If you have any questions, bug reports or comments you can reach me at <a href="https://mastodon.social/@boodoo">@boodoo@m.s</a> or at <a href="https://twitter.com/boodooperson">@boodooperson</a> on the birdsite.</p>
			<ul>
				<li><a href="http://air.decontextualize.com/tracery/">Tracery tutorial</a></li>
				<li><a href="http://www.crystalcodepalace.com/traceryTut.html">Interactive tutorial</a></li>
				<li><a href="http://www.brightspiral.com/tracery/">Tracery visual editor</a></li>
				<li><a href="https://github.com/dariusk/corpora">Useful word collections</a></li>
				<li><a href="https://github.com/v21/tracerybot">Example of a self-hosted bot running on Tracery</a></li>
			</ul>
			<p>In lieu of helping pay for server costs, maintenance and further development, please consider
				<details id="charity_list"><summary><a>Supporting a charity</a></summary>
					<ul id="charities">
						<li><a href="https://barcc.org/join/donation">Boston Area Rape Crisis Center (BARCC)</a></li>
						<li><a href="https://bailproject.org/donate">The Bail Project</a></li>
						<li><a href="https://www.msf.org/donate">Médecins Sans Frontières (Doctors Without Borders)</a></li>
						<li><a href="https://abortionfunds.org/donate">National Network of Abortion Funds</a></li>
						<li><a href="https://www.transyouthequality.org/#block-yui_3_17_2_2_1447266818747_13555">Trans Youth Equality Foundation (TYEF)</li>
						<li><a href="https://vocal.ourpowerbase.net/civicrm/contribute/transact?id=6">VOCAL New York</a></li>
					</ul>
				</details>
			</p>
			</div>
		</div>
		
		<br><br>
	<form id="tracery-form">

	<div class="form-group">
		<label for="tracery">Tracery JSON</label><br>
		<textarea class="form-control expanding" rows="25" id="tracery" name="tracery">
<?php 
		if (is_null($result['tracery']))
		{
			echo('{
	"origin": ["this could be a status", "this is #alternatives# status", "#completely different#"],
	"alternatives" : ["an example", "a different", "another", "a possible", "a generated", "your next"],
	"completely different" : ["and now for something completely different", "so long and thanks for all the fish", "or, maybe, #alternatives# badger"]
}
');
		}
		else
		{
			echo(htmlspecialchars($result['tracery'], 'ENT_HTML5' | ENT_QUOTES , "UTF-8")); /*todo : XSS vuln? */
		}
?>

</textarea>
	</div>
<div id="tracery-validator" class="alert alert-danger hidden" role="alert">Parsing error</div>

	<div class="row">
	<div class="col-md-12">
		<div class="pull-right pad-left">
		<button type="button" id="refresh-generated-status" class="btn btn-default"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></button>
		<button type="button" id="post-generated-status" class="btn btn-post">Post! <span id="generated-status-visibility" class="glyphicon"></span></button>
		</div>
		<div id="generated-status" style="overflow: auto;" class="well well-sm">-----
		<div id="status-media"> 
		</div>
		</div>
		
	</div>
	</div>
<div class="form-inline">
	<div class="form-group">
		
		<select class="form-control" id="frequency" name="frequency">
			<?php 
				$frequencypossibilities = array(-1 => "Never", 10 => "Every 10 minutes", 30 => "Every half hour", 60 => "Every hour", 180 => "Every 3 hours", 360 => "Every 6 hours", 720 => "Twice a day", 1440 => "Once a day", 10080 => "Once a week", 43829 => "Once a month", 525949 => "Once a year");
				foreach ($frequencypossibilities as $freqvalue => $freqlabel) {
					echo('<option value="' . $freqvalue . '" '. ($result['frequency'] == $freqvalue ? 'selected' : '') .'>' . $freqlabel . '</option>');
				}
			?>
		</select>
	</div>

	<div class="form-group">
		post a status as <?php echo('<a class="username" href="'. $result['url']. '">') ?>
			<?php echo('<img src="' . $_SESSION['profile_pic'] . '" width=32> '); ?>
			<span class="username-text"><?php echo($result['username']) ?></span>
			</a>
	</div>
	<br>
	<div class="form-group">
		<select class="form-control" id="visibility" name="visibility">
			<?php
			$visibilities = array("public" => "Public", "unlisted" => "Unlisted", "private" => "Private");
			foreach ($visibilities as $visvalue => $vislabel) {
			  echo('<option value="' . $visvalue . '" '. ($result['visibility'] == $visvalue ? 'selected' : '') .'>' . $vislabel . '</option>');
			}
			?>
		</select>
	</div>

	<div class="form-group">
		visibility for statuses from <?php echo('<a class="username" href="'. $result['url']. '">') ?>
			<?php echo('<img src="' . $_SESSION['profile_pic'] . '" width=32> '); ?>
			<span class="username-text"><?php echo($result['username']) ?></span>
			</a>
	</div>
	<br>
	<div class="form-group">
		
		<select class="form-control" id="is_sensitive" name="is_sensitive">
			<?php 
				$sensitivepossibilities = array(0 => "Innocuous", 1 => "Sensitive");
				foreach ($sensitivepossibilities as $sensitivevalue => $sensitivelabel) {
					echo('<option value="' . $sensitivevalue . '" '. ($result['is_sensitive'] == $sensitivevalue ? 'selected' : '') .'>' . $sensitivelabel . '</option>');
				}
			?>
		</select>
	</div>

	<div class="form-group">
		media is posted as <?php echo('<a class="username" href="'. $result['url']. '">') ?>
			<?php echo('<img src="' . $_SESSION['profile_pic'] . '" width=32> '); ?>
			<span class="username-text"><?php echo($result['username']) ?></span>
			</a>
	</div>
	<br>
	<div class="form-group">
			<select class="form-control" id="does_replies" name="does_replies">
			<?php 
			$replypossibilities = array(1 => "Reply", 0 => "Don't reply");

			foreach ($replypossibilities as $replyvalue => $replylabel) {
				echo('<option value="' . $replyvalue . '" '. ($result['does_replies'] == $replyvalue ? 'selected' : '') .'>' . $replylabel . '</option>');
			}
			?> 
			</select> to statuses sent to <?php echo('<a class="username" href="' . $result['url']. '">') ?>
			<?php echo('<img src="' . $_SESSION['profile_pic'] . '" width=32> '); ?>
			<span class="username-text"><?php echo($result['username']) ?></span>
			</a>

		</div>


</div>
	<div id="reply_rules_container" name = "reply_rules_container" class="form-group <?php echo(($result['does_replies'] ? "": "hidden")) ?>">
<div class="row">
		<div class="col-md-7 col-md-offset-3">
		<br>
			<p>This is also in <a href="https://www.tutorialspoint.com/json/json_syntax.htm">JSON</a> format. When a mention is received, it's checked against the keys (the left hand part) for a match. The keys are specified with <a href="https://developer.mozilla.org/en/docs/Web/JavaScript/Guide/Regular_Expressions">RegExp syntax</a> - this also straightforwardly matches any letters (as long as there's no punctuation). If you want to catch all replies, use <code>"."</code>. The value (the right hand part) is used as the Tracery syntax for the reply - this can be plain text or a symbol such as <code>"#origin#"</code>. Mentions are checked every 5 minutes, and have a 5% chance of being ignored (to prevent bots from responding to each other forever).</p> 
		</div>
		</div>

			<div class="row">
	<div class="col-md-11 col-md-offset-1">

	<div class="form-group">
		<textarea class="form-control" rows="7" id="reply_rules" name="reply_rules">
<?php 
		if (is_null($result['reply_rules']))
		{
			echo('{
	"hello":"hello there!",
	".":"#origin#"
}
');
		}
		else
		{
			echo(htmlspecialchars($result['reply_rules'], 'ENT_HTML5' | ENT_QUOTES , "UTF-8")); 
		}
?>


</textarea>
</div>
	<div id="replyrules-validator" class="alert alert-danger hidden" role="alert">Parsing error</div>
		Test mention: <textarea class="form-control" rows="1" id="test_mention" name="test_mention">@<?php echo($result['username']) ?> </textarea>
		<div class="pull-right pad-left"><br>
		<button type="button" id="refresh-generated-reply" class="btn btn-block btn-default"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></button>
		<button type="button" id="generated-reply-visibility" title="" class="btn btn-block disabled glyphicon"></button>
		</div>
		Response:<div id="generated-reply" style="overflow: auto;" class="well well-sm">-----
		<div id="reply-media"> 
		</div>
		</div>
		
   </div>
  </div>
</div>
	<div class="form-inline">


	<div class="form-group">
			<select class="form-control" id="public_source" name="public_source">
			<?php 
			$sharepossibilities = array(1 => "Share", 0 => "Don't share");

			foreach ($sharepossibilities as $sharevalue => $sharelabel) {
				echo('<option value="' . $sharevalue . '" '. ($result['public_source'] == $sharevalue ? 'selected' : '') .'>' . $sharelabel . '</option>');
			}
			?> 
			</select> Tracery source at <a target="_blank" href="/source/?url=<?php echo($result['url']) ?>"><?=APP_ROOT ?>/source/?url=<?php echo($result['url']) ?></a>.

		</div>
	<br>
	<button id="save-button" class="btn btn-default">Save</button>
	<div class="button form-group pull-right">
		<a class="btn  btn-default logout" href="logout.php"><span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span> Log Out</a>
	</div>    
	
</div>

</form>


			


<!--
		<hr>

		<footer>
		<p>&copy; Company 2015</p>
		</footer>-->
	</div> <!-- /container -->        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
		<script>window.jQuery || document.write('<script src="js/vendor/jquery-1.11.2.min.js"><\/script>')</script>

		<script src="js/vendor/bootstrap.min.js"></script>
		<script src="js/lodash.min.js"></script>
		<script src="js/tracery.js"></script>
		<script src="js/twitter-text-1.9.4.min.js"></script>
		<script src="js/expanding.js"></script>
		<script src="js/json2.js"></script>
		<script src="js/jsonlint.js"></script>
		<script src="js/main.js"></script>
		<script type="text/javascript">var url = `<?php echo($result['url'])?>`</script>
		<script type="text/javascript">
			var orgs = document.getElementById("charities");
			for (var i = orgs.children.length; i >= 0; i--) {
				orgs.appendChild(orgs.children[Math.random() * i | 0]);
			}
		</script>
	</body>
</html>


