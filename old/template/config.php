<?php

/* timezone */
define(TIMEZONE, "America/Chicago");

/* paths */
define("BASE_PATH", "/var/www/assassins");

/* game stats */
define("GAME_START", time() + 86400); // 1427932860
define("REGISTRATION_DEADLINE", GAME_START + 7*86400);

/* database parameters */
define("DATABASE_HOST", "localhost");
define("DATABASE_USER", "assassins_web_user"); // please don't use root
define("DATABASE_PASS", "");
define("DATABASE_NAME", "assassins");

/* general */
define("SMS_EMAIL_FROM_ADDRESS", "updates@m.assassins.in");

define("CARRIERS", array(
	"sprint" => "messaging.sprintpcs.com",
	"tmobile" => "tmomail.net",
	"verizon" => "vtext.com",
	"att" => "txt.att.net",
	"metropcs" => "mymetropcs.com"
)); // this only works as of PHP 7, for PHP 5.6.0 and later use const CARRIERS = array(...);

define("CARRIERS_SUBJECT_SUPPORT", array("metropcs", "tmobile"));