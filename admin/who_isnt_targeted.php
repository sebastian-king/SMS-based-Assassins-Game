<?php
require("../template/top.php");

$players = array();

$q = $db->query("SELECT * FROM players WHERE target > ''");
while ($r = $q->fetch_array(MYSQL_ASSOC)) {
	$players[$r['id']] = array("name"=>$r['name'], "target"=>$r['target']);
}

foreach ($players as $key => $val) {
	$targeted = false;
	foreach ($players as $k => $v) {
		if ($key == $v['target']) {
			$targeted = true;
			break;
		}
	}
	if ($targeted == false) {
		echo $val['name']." is NOT being targeted\n";
	}
}