<?php

require_once("config.php");
require_once(BASE_PATH . "/api/Services/Twilio.php");
require_once(BASE_PATH . "/template/functions.php");

$db = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME)
	or die("Unable to connect to database");
$db->set_charset(DATABASE_CHARSET);

$twilio_client = new Services_Twilio(TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN);
