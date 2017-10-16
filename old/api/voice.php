<?xml version="1.0" encoding="UTF-8"?>
<Response>
	<?php
	if ($_REQUEST["FromCountry"] == "US") {
		$from = str_replace("+1", "", $_REQUEST['Caller']);
	} else {
		die('<Say voice="alice" language="en-GB">We only accept US phone numbers.</Say></Response>');
	}
	require("../template/top.php");
	$q = $db->query("SELECT * FROM players WHERE phone = '".$db->real_escape_string($from)."' LIMIT 1");
	if ($q->num_rows == 1) {
		if (time() < $start) {
			echo '<Say voice="alice" language="en-GB">Assaassins has not yet started.</Say>';
		} else {
			$r = $q->fetch_array(MYSQL_ASSOC);
			echo '<Say voice="alice" language="en-GB">' . uid2name($r['target']) . '</Say>';
		}
	} else {
		echo '<Say voice="alice" language="en-GB">You are not registered for assassins.</Say>';
	}
	?>



</Response>