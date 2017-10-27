<?php
// this file contains useful generic functions that are used throughout the site
function ordinal($num) {
	if (!in_array(($num % 100),array(11,12,13))){
      switch ($num % 10) {
        case 1:  return $num.'st';
        case 2:  return $num.'nd';
        case 3:  return $num.'rd';
      }
    }
    return $num.'th';
}

function ago($time) {
	if ($time > strtotime('midnight', time())) {
		$timestamp = date("g:i a", $time);
	} else if ($time > mktime(0, 0, 0, date("m"), date("d")-1, date("Y"))) {
		$timestamp = "Yesterday, ".date("g:i a", $time);
	} else if ($time > mktime(0, 0, 0, date("m"), date("d")-7, date("Y"))) {
		$timestamp = date("l, g:i a", $time);
	} else {
		$timestamp = date("g:i a, jS F", $time);
		if (date("Y") != date("Y", $time)) {
			$timestamp .= ", " . date("Y", $time);
		}
	}
	return $timestamp;
}

/* token generation functions start */
function crypto_rand_secure($min, $max) {
        $range = $max - $min;
        if ($range < 0) return $min; // not so random...
        $log = log($range, 2);
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);
        return $min + $rnd;
}

function generate_secure_token($length){
    $token = "";
    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
    $codeAlphabet.= "0123456789";
    for($i=0;$i<$length;$i++){
        $token .= $codeAlphabet[crypto_rand_secure(0,strlen($codeAlphabet))];
    }
    return $token;
}

function generate_insecure_token($length = 6) {
	$alpha_numeric = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	return substr(str_shuffle($alpha_numeric), 0, $length);
}
/* token generation functions end */