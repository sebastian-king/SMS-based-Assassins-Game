<?php

/* timezone */
define(TIMEZONE, "America/Chicago");

/* paths */
define("BASE_PATH", getenv('BASE_PATH')); // no trailing slash (set this parameter in the .htaccess file)

/* game stats */
define("GAME_START", strtotime('midnight') + 7*86400); // 1427932860
define("REGISTRATION_DEADLINE", strtotime('midnight') + 4*86400);

/* database parameters */
define("DATABASE_HOST", "localhost");
define("DATABASE_USER", "assassins_web_user"); // please don't use root
define("DATABASE_PASS", "HDR3Z22c6bWyNZwJdkeCPAWL");
define("DATABASE_NAME", "assassins");
define("DATABASE_CHARSET", "utf8");

/* api keys */
define("TWILIO_ACCOUNT_SID", "AC9a67afc3c60366f598293b56054f00b3");
define("TWILIO_AUTH_TOKEN", "c8e6e74915113ace8ee18e6168e58a91");

/* general */
define("GAME_NAME", "Assassins DEMO");
define("SITE_SUPERDOMAIN", "assassins.in"); // please make sure that this is the top level domain that you will be operating on, and not www.
define("ADMIN_NAME", "Seb"); // this is used mostly as a signature for friendly messages
define("PHONE_NUMBER", "8173693691"); // digits only here please, no brackets or dashes

define("SMS_EMAIL_FROM_ADDRESS", "updates@m.assassins.in");
define("TWITTER_HANDLE", "assassins2k15");
define("SUPPORT_EMAIL", "support@assassins.in");

define("EMAIL_DOMAIN", "assassins.in");
define("EMAIL_USER", "support");
define("EMAIL_NAME", "Assassins DEMO");

/* other config - don't change unless you know what you are doing */
define("CARRIERS", array(
	"sprint" => "messaging.sprintpcs.com",
	"tmobile" => "tmomail.net",
	"verizon" => "vtext.com",
	"att" => "txt.att.net",
	"metropcs" => "mymetropcs.com"
)); // this only works as of PHP 7, for PHP 5.6.0 and later use const CARRIERS = array(...);
define("CARRIERS_SUBJECT_SUPPORT", array("metropcs", "tmobile"));

