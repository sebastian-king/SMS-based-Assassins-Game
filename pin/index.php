<?php
require("../template/top.php");
session_start();
$p = base64_decode($_GET['q']);
$uid = $p;
$q = $db->query("SELECT * FROM players WHERE id = '".$db->real_escape_string($uid)."' LIMIT 1") or die("Database error, please let us know of this URL.");
if ($q->num_rows == 1) {
	$r = $q->fetch_array(MYSQLI_ASSOC);
	if (!empty($r['pin'])) {
		$error = "Your PIN is already set. If you think you are getting this message in error, please contact us on <a href='https://twitter.com/Assassins2k15'>Twitter</a>.";
	}
} else {
	$error = "There is no user here, you must have the wrong link, sorry.";
}
?><!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php echo (($r['name']) ? $r['name'] : "?"); ?> | MHS Assassins 2015</title>
		
		<!-- Stylesheets -->
		<link href="/pin/css/bootstrap.min.css" rel="stylesheet">
		<link href="/pin/css/font-awesome.min.css" rel="stylesheet">
		<link href="/pin/css/bootstrapValidator.min.css" rel="stylesheet">
		<link href="/pin/css/ladda-themeless.min.css" rel="stylesheet">
		<link href="/pin/css/animate.min.css" rel="stylesheet">
		<link href="/pin/css/owl.carousel.css" rel="stylesheet">
		<link href="/pin/css/owl.theme.css" rel="stylesheet">
		<link href="/pin/css/app.css" rel="stylesheet">
		
		<!-- Favicon -->
		<link rel="shortcut icon" href="/pin/img/favicon/favicon.ico" type="image/x-icon">
		<link rel="icon" href="/favicon.ico" type="image/x-icon">
		
		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
			<script src="/pin/js/html5shiv.js"></script>
			<script src="/pin/js/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>
		<!-- Preloader -->
		<div id="preloader">
			<div id="status" class="text-center">
				<div class="spinner">
				  <div class="rect1"></div>
				  <div class="rect2"></div>
				  <div class="rect3"></div>
				  <div class="rect4"></div>
				  <div class="rect5"></div>
				</div>
			</div>
		</div>
		
		<!-- Main -->
		<section class="main text-center" id="home">
			<div class="page">
				<div class="wrapper">
					<div class="container">
						<h1 class="heading">Assassins 2015</h1>
						<p>
							Here you can set your PIN for the game. During the game if you wish to leave or if you get assassinated, you will be required to type in your PIN as an extra security measure to ensure it is actually you that is confirming the exit from the game.
						</p>
						<div class="row">
							<div id="countdown"></div>
						</div>
						<!-- Subscription form -->
                        <?php if (!@$error) { ?>
						<form class="form-inline signup" action="/pin/set.php" role="form" id="signupForm" method="POST">
							<div class="form-group">
								<input type="text" name="pin" class="form-control" placeholder="PIN Number"/>
                                <input type="hidden" name="id" value="<?php echo $uid; ?>"/>
							</div>
							<div class="form-group">
								<button type="submit" class="btn btn-theme ladda-button" data-style="expand-left"><span class="ladda-label">Set PIN</span></button>
							</div>
						</form>
                        <?php } else { ?>
                        <div class="alert alert-danger" role="alert"><b>Error!</b> <?php echo $error; ?></div>
                        <?php } ?>
						<footer class="text-center">
							<div class="social">
								<a href="https://twitter.com/Assassins2k15" class="btn btn-theme"><i class="fa fa-twitter"></i></a>
							</div>
						</footer>
					</div>
				</div>
			</div>
		</section>
		
		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="/pin/js/jquery.min.js"></script>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="/pin/js/bootstrap.min.js"></script>
		<script src="/pin/js/jquery.backstretch.min.js"></script>
		<script src="/pin/js/jquery.countdown.min.js"></script>
		<script src="/pin/js/bootstrapValidator.min.js"></script>
		<script src="/pin/js/validator/PIN.js"></script>
		<script src="/pin/js/spin.min.js"></script>
		<script src="/pin/js/ladda.min.js"></script>
		<script src="/pin//pin/js/retina.min.js"></script>
		<script src="/pin/js/wow.min.js"></script>
		<script src="/pin/js/owl.carousel.min.js"></script>
		<script src="/pin/js/init.js"></script>
	</body>
</html>