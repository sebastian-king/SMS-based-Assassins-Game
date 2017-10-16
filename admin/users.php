<?php
require("../template/top.php");

if (isset($_GET['toggle'])) {
	$id = $_GET['toggle'];
	$q = $db->query("SELECT * FROM players WHERE id = '".$db->real_escape_string($id)."'") or die($db->error);
	$r = $q->fetch_array(MYSQL_ASSOC);
	if ($r['senior'] == "idk") {
		$s = "yes";
	} else if ($r['senior'] == "yes") {
		$s = "no";
	} else {
		$s = "idk";
	}
	$db->query("UPDATE players SET senior = '$s' WHERE id = '".$db->real_escape_string($id)."' LIMIT 1") or die($db->error);
	header("Location: /tmp/users.php");
	die();
}

echo "<body style='line-height: 18px'><pre>\nNAME			  			EMAIL						   SENIOR\n";

$i = 1;

$q = $db->query("SELECT * FROM players WHERE target > '' ORDER BY name ASC");
while ($r = $q->fetch_array(MYSQL_ASSOC)) {
	//if (!preg_match("/^(.+)[ ]+(.+)$/i", $r['name'])) {
		preg_match( '/^(\d{3})(\d{3})(\d{4})$/', $r['phone'],  $matches );
		$result = '('.$matches[1] . ')-' .$matches[2] . '-' . $matches[3];
		$q2 = $db->query("SELECT count(*) FROM assassinations WHERE assassin = '".$db->real_escape_string($r['id'])."'");
		$r2 = $q2->fetch_array(MYSQL_NUM);
		$q3 = $db->query("SELECT * FROM players WHERE id = '".$db->real_escape_string($r['target'])."' LIMIT 1");
		$r3 = $q3->fetch_array();
		echo "<p " . (($i%2==0) ? " style='background-color:#e1e1e1'" : "") . "> ".str_pad($i, 3, 0, STR_PAD_LEFT)." (".str_pad($r['validated'], 5).") " . str_pad($r['name']."({$r['id']})", 25, " ") . " => " . str_pad($r2[0], 20, " ") . " (".$r['pin'].") [{$r3['assassinated']}] => ".str_pad(uid2name($r['target'])."({$r['target']})", 25, " ")." => (<a href='?toggle={$r['id']}'>" . $r['email'] . "</a> ".str_pad("<a href='tel:{$r['phone']}'>".$result."</a>", 17, " ", STR_PAD_LEFT).")</p>\n";
		$i++;
	//}
}