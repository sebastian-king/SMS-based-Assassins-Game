<?php
require("../template/top.php");
$q = $db->query("SELECT * FROM pictures WHERE uid = '287' AND used = 0 ORDER BY id DESC LIMIT 1");
$p = $q->fetch_array();
$media = array("uri" => $base . $p['assassins_url'], "filename" => "test", "mime" => $p['mime']);
echo '<pre>';
var_dump(tweet("test", $media)); // hashtag?