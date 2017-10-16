<?php

require_once("config.php");

date_default_timezone_set(TIMEZONE);

$db = mysqli_connect(DATABASE_HOST,DATABASE_USER,DATABASE_PASS,DATABASE_NAME);// or die("Unable to connect to database");

/*
require_once("$base/api/twitter/codebird.php");
 
\Codebird\Codebird::setConsumerKey("GIjTAMcPAhjMEM1rnVhNw3orz", "4ldTHeIhvsVWtJBjV0hhwkNntJKbDkq5Fo75c04eho2VxepVL7");
$cb = \Codebird\Codebird::getInstance();
$cb->setToken("3094868766-EhCY96WUS8iGMh3SpIkUNbvPqlz1mSPP6l1Pa6X", "stBBLNrMFYc3y44sChRtsn445IrG6pgycFDjHE0ullkYN");
*/

require_once("functions.php");
