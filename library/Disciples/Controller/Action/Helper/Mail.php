<?php
/**
 * Mail.php
 *
 * @name    Mail.php
 * @package Disciples
 * @author  Sangwoo Han <linkedKorean@gmail.com>
 */
/**
 * Mail - Action helper for the mailing service
 *
 * @package Disciples
 * @author  Sangwoo Han <linkedKorean@gmail.com>
 */
class Disciples_Controller_Action_Helper_Mail extends Zend_Controller_Action_Helper_Abstract
{
    public function __construct()
    {       
    
    }
    
    public function sendMail($to, $subject, $body)
    {
        $mailer = new PHPMailer();
        
        $mailer->IsSMTP();                   // telling the class to use SMTP
        
        $mailer->SMTPDebug  = 0;             // enables SMTP debug information (for testing)
                                             //   0 = no debug info
                                             //   1 = errors and messages
                                             //   2 = messages only
        $mailer->SMTPAuth   = true;          // enable SMTP authentication
        $mailer->SMTPSecure = MAIL_SECURE;                   
        $mailer->Host       = MAIL_HOST;     // SMTP server
        $mailer->Port       = MAIL_PORT;     // set the SMTP port for the GMAIL server
        $mailer->Username   = MAIL_USERNAME; // GMAIL username
        $mailer->Password   = MAIL_PASSWORD; // GMAIL password
        
        $mailer->SetFrom('noreply@antioch.org', 'Antioch Disciples');
        
        //$mail->AddReplyTo("user2@gmail.com', 'First Last");
        
        $mailer->Subject    = $subject;
        
        //$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
        
        $mailer->MsgHTML($body);
        
        if (is_array($to)) {
        	foreach ($to as $address) {
        		$mailer->AddAddress($address);
        	}
        } else {
        	$mailer->AddAddress($to);
        }
                
        //$mail->AddAttachment("images/phpmailer.gif");      // attachment
        //$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment
        
        if (!$mailer->Send()) {
            return "Mailer Error: " . $mailer->ErrorInfo;
        }
        
        return false;
    }
}