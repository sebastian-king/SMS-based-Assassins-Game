<?php
require("../template/top.php");

$a = new stdClass();

$q = $db->query("SELECT count(*) FROM players");
$r = $q->fetch_array(MYSQLI_NUM);
$a->registered_users = $r[0];
$q = $db->query("SELECT count(*) FROM players WHERE target > ''");
$r = $q->fetch_array(MYSQLI_NUM);
$a->alive_players = $r[0];
$a->dead_players = $a->registered_users - $a->alive_players;
$q = $db->query("SELECT count(*) FROM assassinations WHERE target = assassin");
$r = $q->fetch_array(MYSQLI_NUM);
$a->suicides = $r[0];
$q = $db->query("SELECT count(*) FROM assassinations WHERE target != assassin");
$r = $q->fetch_array(MYSQLI_NUM);
$a->assassinations = $r[0];

$top10 = rank(NULL, 10);
foreach ($top10 as $key => $val) {
	$a->top10[] = array(uid2name($val['id']), $val['kills'], $val['killed']);
}

echo json_encode($a);