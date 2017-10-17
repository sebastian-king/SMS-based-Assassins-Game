<?php

require(getenv('BASE_PATH') . "/template/top.php");

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
$token = generate_secure_token(26);
$email_verification_token = strtolower(generate_insecure_token(6));
$phone_verification_token = strtolower(generate_insecure_token(6));

$db->query("
INSERT INTO `players`
(`name`,`email`,`phone`,`uid`,`verify_email`,`verify_phone`)

VALUES
('".$db->real_escape_string($name)."',
'".$db->real_escape_string($email)."',
'".$db->real_escape_string($phone)."',
'".$token."',
'".$email_verification_token."',
'".$phone_verification_token."');
") or die(0);

$e = email($email,
		   "Confirm your entry to " . GAME_NAME . "!",
		   "Hello, ".current(explode(" ", $name))."!
		   <br><br>
		   Welcome to " . GAME_NAME . "!
		   <br><br>
		   <a href='https://{SITE_SUPERDOMAIN}/u/$token/v$email_verification_token'>Click here to confirm your e-mail address.</a>
		   <br><br>
		   <b>Please note:</b> You will also have to confirm your phone number, if you do not receive a text message from us soon then please text <b>{$db->insert_id}#$email_verification_token</b> to ".format_phone_number(PHONE_NUMBER)." to confirm your phone number.
		   <br><br>
		   Best regards,
		   <br>" . ADMIN_NAME); 
//error_log("EMAIL_SEND=$e=" . $email . "\n", 3,"mail.log"); // just in case

try {
        $sms = $twilio_client->messages->create(
                $phone,
                array(
                        'from' => PHONE_NUMBER,
                        'body' => "Hello ".current(explode(" ", $name)).", welcome to " . GAME_NAME . ". Please reply with the code '$phone_verification_token' to confirm your phone number. Thank you."
                )
        );
} catch (Twilio\Exceptions\RestException $e) {
	error_log($e);
}

if (!$e) {
	error_log("Unable to send e-mail for user #{$db->insert_id}.");
}
if (!@$sms) {
	error_log("Unable to send SMS for user #{$db->insert_id}.");
}

echo 1;
