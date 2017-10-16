<?php
require("../template/top.php");
session_start();
$u = $_GET['q'];
$q = $db->query("SELECT * FROM players WHERE uid = '".$db->real_escape_string($u)."' LIMIT 1") or die("Database error, please let us know of this URL.");
if ($q->num_rows == 1) {
	$u = $q->fetch_array(MYSQL_ASSOC);
	if (isset($_GET['v'])) {
		if ($_GET['v'] == $u['vere']) {
			if ($u['validated'] == 'phone' || $u['validated'] == 'both') {
				$validated = 'both';
			} else {
				$validated = 'email';
			}
			$db->query("UPDATE players SET validated = '$validated' WHERE id = '{$u['id']}' LIMIT 1");
			$_SESSION['email_confirmed'] = true;
			header("Location: /u/{$u['uid']}");
			die();
		} else {
			$error = "The verification code you entered was not valid, please try again. If you are having problems getting verified please contact us on <a href='https://twitter.com/Assassins2k15'>Twitter</a>.";
		}
	}
	$title = $u['name'] . "'s Stats | MHS Assassins 2015";
} else {
	$u = array("id"=>-1,"uid"=>-1,"name"=>"Literally Nobody");
	$title = "No player found | MHS Assassins 2015";
	$error = "There is no user here, you must have the wrong link, sorry.";
	error_log("No user at: ".$_SERVER['REQUEST_URI']);
}
?><!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title><?php echo $title; ?></title>

		<link href='http://fonts.googleapis.com/css?family=Lato:400,700,900' rel='stylesheet' type='text/css'>
		<link href="//netdna.bootstrapcdn.com/font-awesome/3.1.1/css/font-awesome.css" rel="stylesheet">
		<link rel="stylesheet" href="/user/css/lib/bootstrap.min.css">
		<link rel="stylesheet" href="/user/css/lib/bootstrap-responsive.min.css">
		<link rel="stylesheet" href="/user/css/main.css">
	</head>

	<body>
		<div id="fb-root"></div>

		<div class="container">
        	<?php 
			if (isset($error)) {?><center><div class="alert alert-danger" role="alert" style="font-size:large;font-weight:800;display:inline-block;"><?php echo $error; ?></div></center><?php }
			else
			if (@$_SESSION['email_confirmed'] == true) {$_SESSION['email_confirmed']=false;?><center><div class="alert alert-success" role="alert" style="font-size:large;font-weight:800;display:inline-block;">Your e-mail address has been confirmed!<br><?php if ($u['validated'] == 'both') { "You are now completely verified and ready for MHS Assassins 2015! :)"; } else { echo " Please don't forget to verify your phone number, you cannot play without it. <br>If you are having trouble getting verified please contact us on <a href='https://twitter.com/Assassins2k15'>Twitter</a>."; } ?></div></center><?php }
			 ?>
			<div class="row row-main">
				<div class="span5 offset2 main-content">
					<img src="/user/img/logo.png" alt="Logo" width="80" height="80" class="top-logo visible-phone">

					<h1><?php
                    foreach (explode(" ", $u['name']) as $key => $val) {
						echo "<span>$val</span> ";
					}
					?></h1>
                    
                     <?php
					$k = 0;
					$q = $db->query("SELECT * FROM assassinations WHERE target = '{$u['id']}' AND ver = 'both'");
					while ($r = $q->fetch_array(MYSQL_ASSOC)) {
						if ($r['assassin'] == $r['target']) {
							?>
                            <div class="content">
                                <div class="initial-content">
                                    <p><?php echo ago($r['timestamp']) . " - " . uid2name($u['id']) . " committed suicide."; ?></p>
                                </div>
                            </div>
							<?php
							$k++;
						} else if ($r['assassin'] == 0) { //system
							?>
                            <div class="content">
                                <div class="initial-content">
                                    <p><?php echo ago($r['timestamp']) . " - " . uid2name($u['id']); ?> was disqualified.</p>
                                </div>
                            </div>
                            <?php
						} else {
							$k++;
							?>
                            <div class="content">
                                <div class="initial-content">
                                    <p><?php echo ago($r['timestamp']) . " - " . uid2name($u['id']) . " was assassinated by " . uid2name($r['assassin']) . "."; ?></p>
                                </div>
                            </div>
							<?php
						}
					}
					?>
                    <?php
					$q = $db->query("SELECT * FROM assassinations WHERE assassin = '{$u['id']}' AND ver = 'both' ORDER BY id DESC");
					while ($r = $q->fetch_array(MYSQL_ASSOC)) {
						if ($r['assassin'] == $r['target'] || $r['assassin'] == 0) { continue; }
						$k++;
						?>
                        <div class="content">
                            <div class="initial-content">
                                <p><?php echo ago($r['timestamp']) . " - " . uid2name($u['id']) . " assassinated " . uid2name($r['target']) . "."; ?></p>
                            </div>
                        </div>
                        <?php
					}
					if ($k == 0) {
						?>
                        <div class="content">
                            <div class="initial-content">
                                <p><?php echo $u['name']; ?> hasn't yet gotten any kills, but is still in the game!</p>
                            </div>
                        </div>
                        <?php
					}
					?>
                    <!--<div class="content">
                        <div class="initial-content">
                        	<p>On this page your statistics will be displayed throughout the game, each assassination you get will be logged here, for example:</p>
                            <p><?php echo ago(time()-300) . " - " . $u['name'] . " assassinated Garrett Odom."; ?></p>
                            <p>or</p>
                            <p><?php echo ago(time()+300) . " - " . $u['name'] . " was assassinated by Sebastian King."; ?></p>
                            <p>Only people with the <a href='http://assassins.in/u/<?php echo $u['uid']; ?>'>link to this page</a> can see it, so if you don't want anyone to see your stats just don't share this page. Alternatively, you can <a href='http://twitter.com/home?status=Checkout%20my%20assassins%20stats%21%20http://assassins.in/u/<?php echo $u['uid']; ?>%20%23mhsassassins2015%20%40assassins2k15'>tweet this page right now</a> so people can watch those kills stack up!
                        </div>
					</div>-->
						<div class="visible-phone phone-countdown">
							<div class="countdown-item">
								<div class="countdown-number countdown-days">0</div>
								<div class="countdown-value">kills</div>
							</div>
                            
                            <div class="countdown-item">
                                <div class="countdown-number countdown-minutes">88th</div>
                                <div class="countdown-value">position</div>
                            </div>
					</div>
				</div>

				<div class="span5 hidden-phone">
					<div class="phone">
						<img alt="Phone" src="/user/img/phone.png">
					
						<div class="phone-screen">
							<img alt="Logo" src="/user/img/logo.png" width="80" height="80" class="logo">
                            
                            <?php
							$stats = rank($u['id']);
							$rank = $stats['rank'];
							$kills = $stats['kills'];
							$rank = ordinal($rank);
							?>

							<div class="phone-countdown">
								<div class="countdown-item">
									<div class="countdown-number countdown-days"><?php echo $kills; ?></div>
									<div class="countdown-value">kills</div>
								</div>	

								<div class="countdown-item">
									<div class="countdown-number countdown-minutes"><?php echo $rank ?></div>
									<div class="countdown-value">position</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>	
			
		</div>
		
		<script src="/user/js/lib/jquery.min.js"></script>
		<script src="/user/js/lib/countdown.js"></script>
		<script src="/user/js/main.js"></script>
	</body>
</html>