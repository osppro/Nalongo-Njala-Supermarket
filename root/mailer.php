<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function GoMail($to,$subj,$body){
	$mail = new PHPMailer(true);
	$mail->SMTPDebug = 0;                           // Enable verbose debug output
	$mail->isSMTP();								// Set mailer to use SMTP
	$mail->Host = "mail.penielbeachhotel.com";		// Specify main and backup SMTP servers
	$mail->SMTPAuth = true;							// Enable SMTP authentication
	$mail->Username = "info@penielbeachhotel.com";	// SMTP username
	$mail->Password = "Peniel@HotelEntebbe";    	// SMTP password
	$mail->SMTPSecure = "ssl";						// Enable TLS encryption, `ssl` also accepted
	$mail->Port = 465;								// TCP port to connect to
	// Recipients
	$mail->setFrom('info@penielbeachhotel.com', 'PENIEL BEACH HOTEL ENTEBBE');
	$mail->addAddress($to, $to);					// Add a recipient
	//$mail->addCC($cc, $cc);
	// Content
	$mail->isHTML(true);							// Set email format to HTML
	$mail->Subject = $subj;
	$mail->Body    = $body;
	// check if mail has been sent
	if($mail->send()) {
		$_SESSION['message'] = "Mail has been successfully sent.";
	} else {
		$_SESSION['message'] = "Sending failed:" . $mail->ErrorInfo;
	}
}
