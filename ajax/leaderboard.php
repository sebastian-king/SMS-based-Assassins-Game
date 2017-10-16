<?php
require("../template/top.php");

$a = new stdClass();

$all = rank(NULL, 1000); // ?
foreach ($all as $key => $val) {
	$a->all[] = array(uid2name($val['id']), $val['kills'], $val['killed'], uid2uid($val['id']));
}

echo json_encode($a);