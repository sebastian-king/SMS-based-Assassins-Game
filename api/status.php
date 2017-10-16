<?php 
ob_start();
var_dump($_POST);
$result = ob_get_clean();
error_log($result, 3, "calls.log"); 
?>