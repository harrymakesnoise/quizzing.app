<?
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class QuizEmail {
  public function __construct() {
    global $quizSite;
    
    $this->mail = new PHPMailer(true);
    
    $this->sitename = $quizSite->sitename;  
    //Server settings
    $this->mail->isSMTP();                                            // Send using SMTP
    $this->mail->Host       = '10.0.1.9';                       // Set the SMTP server to send through
    $this->mail->SMTPAuth   = true;                                   // Enable SMTP authentication
    $this->mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
    $this->mail->SMTPSecure = "tls";
    $this->mail->SMTPOptions = array(
          'ssl' => array(
              'verify_peer' => false,
              'verify_peer_name' => false,
              'allow_self_signed' => true
          )
      );
    $this->mail->setFrom('no-reply@digiavit.co.uk', $this->sitename);
    $this->mail->AddReplyTo('no-reply@digiavit.co.uk', $this->sitename);
    $this->mail->isHTML(true);                                  // Set email format to HTML
  }
  
  public function sendWelcomeEmail($selector, $token, $teamname, $email) {
    $activationLink = WEBSITE_HOMEURL . '/login/' . $selector . '/' . $token;
    $find = array('####SITENAME####', '####ACTIVATELINK####', '####USERNAME####', '####SITEASSETS####');
    $rep  = array($this->sitename, $activationLink, $teamname, WEBSITE_HOMEURL . '/siteassets');

    $this->mail->Body    = file_get_contents(INCLUDES_PATH . '/email/welcome.html');
    $this->mail->Body    = str_replace($find, $rep, $this->mail->Body);
    $this->mail->Subject = 'Welcome to ' . $this->sitename;
    $this->mail->addAddress($email, $teamname);
    $this->mail->send();
  }
  
  public function sendPasswordResetEmail($selector, $token, $email) {
    $resetLink = WEBSITE_HOMEURL . '/forgot-password/' . $selector . '/' . $token;
    $find = array('####SITENAME####', '####RESETLINK####', '####USERNAME####', '####SITEASSETS####');
    $rep  = array($this->sitename, $resetLink, 'Quiz Player', WEBSITE_HOMEURL . '/siteassets');

    $this->mail->Body    = file_get_contents(INCLUDES_PATH . '/email/password-reset.html');
    $this->mail->Body    = str_replace($find, $rep, $this->mail->Body);
    $this->mail->Subject = 'Reset your ' . $this->sitename . ' password';
    $this->mail->addAddress($email);
    $this->mail->send();
  }
}
?>