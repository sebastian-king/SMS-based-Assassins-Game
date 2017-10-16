<?php

require("../template/top.php");
require("../api/Services/Twilio.php");

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

function getToken($length){
    $token = "";
    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
    $codeAlphabet.= "0123456789";
    for($i=0;$i<$length;$i++){
        $token .= $codeAlphabet[crypto_rand_secure(0,strlen($codeAlphabet))];
    }
    return $token;
}

function generate_random_string($name_length = 6) {
	$alpha_numeric = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	return substr(str_shuffle($alpha_numeric), 0, $name_length);
}

$fields = array(
	"name" => array("invalid"=>4),
	"email" => array("invalid"=>2,"duplicate"=>5), 
	"phone" => array("invalid"=>3,"duplicate"=>6)
);

foreach ($fields as $key => $ret) {
	$val = @$_POST[$key];
	if (empty($val)) {
		die((string)$ret["invalid"]);
	}
	if (isset($ret["duplicate"])) {
		$q = $db->query("SELECT * FROM players WHERE ".$key." = '".$db->real_escape_string($val)."' LIMIT 1") or die("0");
		if ($q->num_rows != 0) {
			die((string)$ret["duplicate"]);
		}
	}
	switch ($key) {
		case "email":
			filter_var($val, FILTER_VALIDATE_EMAIL) or die((string)$ret["invalid"]);
		break;
		case "phone":
			$val = preg_replace('/\s+(#|x|ext(ension)?)\.?:?\s*(\d+)/', ' ext \3', $val);
			preg_match('/^(\+\s*)?((0{0,2}1{1,3}[^\d]+)?\(?\s*([2-9][0-9]{2})\s*[^\d]?\s*([2-9][0-9]{2})\s*[^\d]?\s*([\d]{4})){1}(\s*([[:alpha:]#][^\d]*\d.*))?$/', $val, $m) or die((string)$ret["invalid"]);
		break;
	}
}

$name = ucwords($_POST['name']);
$email = $_POST['email'];
$phone = $m[4] . $m[5] . $m[6]; // extension will be dropped completely
$token = getToken(26);
$vere = strtolower(generate_random_string(6));
$verp = strtolower(generate_random_string(6));

$db->query("INSERT INTO `players` (`name`,`email`,`phone`,`uid`,`vere`,`verp`) VALUES ('".$db->real_escape_string($name)."','".$db->real_escape_string($email)."','".$db->real_escape_string($phone)."','".$token."','".$vere."','".$verp."');
") or die("0");

$e = email($email, "Confirm your entry to MHS Assassins 2015!", "Hello, ".current(explode(" ", $name))."!<br><br>Welcome to MHS Assassins 2015!<br><a href='http://assassins.in/u/$token/v$vere'>Click here to confirm your e-mail address.</a><br><b>Please note:</b> You will also have to confirm your phone number, if you do not receive a text message from us soon then please text <b>{$db->insert_id}-$vere</b> to (817)-369-3691 to confirm your phone number.<br><br>Best regards,<br>Seb, Ben & Sam"); 
error_log("EMAIL_SEND=$e=" . $email . "\n",3,"mail.log"); // just in case
 
$client = new Services_Twilio("AC220f04b1ab251758b0cb7279b1184cc9", "32a60dc935f29520ef43c77f3b6e2a36");

$sms = $client->account->messages->sendMessage(
	"8173693691", 
	$phone,
	"Hello, ".current(explode(" ", $name)).". Welcome to MHS Assassins 2015. Please reply with the code '$verp' to confirm your phone number."
);

echo "1";