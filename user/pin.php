<?php
require("../template/top.php");
session_start();
$p = base64_decode($_GET['q']);
$salt = "Ca(gE^igY0K-J9puJLKo3*aonWzFi^EPPn_7^87H";
$uid = end(explode($salt, $p));
$q = $db->query("SELECT * FROM players WHERE id = '".$db->real_escape_string($uid)."' LIMIT 1") or die("Database error, please let us know of this URL.");
if ($q->num_rows == 1) {
	$r = $q->fetch_array(MYSQL_ASSOC);
	if (!empty($r['pin'])) {
		$error = "Your PIN is already set. If you think you are getting this message in error, please contact us on <a href='https://twitter.com/Assassins2k15'>Twitter</a>.";
	}
} else {
	$error = "There is no user here, you must have the wrong link, sorry.";
}

echo $error;

?>