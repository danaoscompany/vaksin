<?php

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Test extends CI_Controller {
	
	public function email() {
		$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->SMTPDebug = 2;                      // Enable verbose debug output
    $mail->isSMTP();                                            // Send using SMTP
    $mail->Host       = 'localhost';                    // Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
    $mail->Username   = '_mainaccount@rumahvaksincikarang.com';                     // SMTP username
    $mail->Password   = 'V4ks1n2020';                               // SMTP password
    $mail->SMTPSecure = 'tls';         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
    $mail->Port       = 465;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

    //Recipients
    $mail->setFrom('_mainaccount@rumahvaksincikarang.com', 'Mailer');
    $mail->addAddress('danaoscompany@gmail.com', 'Joe User');     // Add a recipient
    $mail->addReplyTo('_mainaccount@rumahvaksincikarang.com', 'Information');
    $mail->addCC('cc@example.com');
    $mail->addBCC('bcc@example.com');
    // Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = 'Here is the subject';
    $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
	}
	
	public function delete_image() {
		if (file_exists("userdata/testgambar.jpg")) {
			unlink("userdata/testgambar.jpg");
		}
	}
	
	public function a() {
		$slot = $this->db->query("SELECT * FROM `slots` WHERE `id`=109")->row_array();
		$date = '2021-01-10 10:00:00';
	    $date = substr($date, 0, strpos($date, ' '));
	    $latestSlotID = intval($this->db->query("SELECT `id` FROM `slots` WHERE DATE(`start_date`)='" . $date . "' ORDER BY `start_date` DESC LIMIT 1")->row_array()['id']);
	    $usedVaccines = $this->db->query("SELECT * FROM `used_vaccines` WHERE `slot_id`=" . $latestSlotID . " ORDER BY `no_anggota` DESC LIMIT 1")->result_array();
	    $noAnggota = 0;
    	if (sizeof($usedVaccines) > 0) {
      		$noAnggota = $usedVaccines[0]['no_anggota'];
      		$noAnggota = intval($noAnggota)+1;
    	}
	    $noAnggota = str_pad($noAnggota, 4, '0', STR_PAD_LEFT);
    	//echo $noAnggota;
    	echo sizeof($usedVaccines);
	}
}
