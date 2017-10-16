<?php
require("../template/top.php");

$pin = $_POST['pin'];
$id = $_POST['id'];

$a = new stdClass();

$q = $db->query("SELECT * FROM players WHERE id = '".$db->real_escape_string($id)."' LIMIT 1");
if ($q->num_rows == 1) {
	$r = $q->fetch_array(MYSQL_ASSOC);
	if (preg_match("/\d{4}/", $pin)) {
		$db->query("UPDATE players SET pin = '".$db->real_escape_string($pin)."' WHERE id = '".$db->real_escape_string($id)."' LIMIT 1");
		$a->message = "Done!";
	} else {
		$a->message = "Error, pin must be 4 digits only";
	}
} else {
	$a->message = "Error!";
}


echo json_encode($a);
?>