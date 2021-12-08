<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$username = "xxxxxxxxxxxx";
$password = "xxxxxxxxxxxx";
$from_email = "abc@example.com";
$from_name = "Bloom inn";

$to = 'def@example.com';
$cc = 'ghi@example.com';
$bcc = 'xyz@example.com';
$subject = 'Contact form';

$host = "email-smtp.us-east-1.amazonaws.com";
$port = xxx;

$thankyou_mail_subject = 'Bloom inn - Thank you for contacting us!';
$thankyou_mail_body = file_get_contents('thanks.html');

//*********script********//

$data = $_POST;

$resp = [];
if($to =='' && (!isset($data['email_to']) || $data['email_to'] == '')) {
	$resp['error'] = 'You must provide at least one recipient email address';
	echo $resp['error']; die();
}

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

if(isset($data['email_to']) && $data['email_to'] != '') {
	$to = $data['email_to'];
}

if(isset($data['email_cc']) && $data['email_cc'] != '') {
	$cc = $data['email_cc'];
}

if(isset($data['email_bcc']) && $data['email_bcc'] != '') {
	$bcc = $data['email_bcc'];
}

if(isset($data['subject']) && $data['subject'] != '') {
	$subject = $data['subject'];
}

$body = '';
$body .= 'Form submitted : '.date('Y-m-d H:i:s'); 
foreach ($data as $k => $v) {
	if($k != 'email_to' && $k != 'email_cc' && $k != 'email_bcc' && $k != 'subject' && $k != 'returnurl' && $k != 'submit') {
		$body .= '<br>'.$k.' : '.$v;
	}
}

$send_mail = sendemail($to, $body, $subject, $cc, $bcc);

if($send_mail){
	if(isset($data['email']) && $data['email'] != '') {
		sendemail($data['email'], $thankyou_mail_body, $thankyou_mail_subject);	
	}

	if(isset($data['returnurl']) && $data['returnurl'] != '') {
		header('Location:'.$data['returnurl']);
		exit();
	} else {
		echo 'Email has been sent successfully';
		die();
	}
}else{
    echo 'Email has not been sent';
	die();
}

function sendemail($to, $body, $subject='', $cc='', $bcc='') {
	global $username, $password, $port, $host, $from_email, $from_name;

	$mail = new PHPMailer(true);
	$mail->isSMTP();
	$mail->SMTPAuth = true;
	$mail->SMTPKeepAlive = true;
	$mail->Mailer = "smtp";  

	$mail->Host       = $host;
	$mail->SMTPDebug  = 0; 
	$mail->isHTML(true); 
	$mail->SMTPSecure = "PHPMailer::ENCRYPTION_STARTTLS"; 
	$mail->Port       = 587; 
	$mail->Username   = $username;
	$mail->Password   = $password;
	$mail->setFrom($from_email, $from_name);


	if($to != '') {
		$mail->AddAddress(str_replace(' ', '', $to));
	}

	if($cc != '') {
		$cc_mail = explode(',', $cc);
		for($j=0;$j<count($cc_mail);$j++){
			$mail->AddCC(str_replace(' ', '', trim($cc_mail[$j])));
		}
	}

	if($bcc != '') {
		$bcc_mail = explode(',', $bcc);
		for($j=0;$j<count($bcc_mail);$j++){
			$mail->Addbcc(str_replace(' ', '', trim($bcc_mail[$j])));
		}
	}

	if($subject != '') {
		$mail->Subject = $subject;
	}

	$mail->msgHTML($body); // optional - MsgHTML will create an alternate automatically
	if($mail->Send()) {
		return 1;
	} else {
		return 2;
	}
}

?>