<?php
prado::using ('Application.logic.Logic_Global');
class Logic_Mail extends Logic_Global {	
    /**
     * untuk menyimpan object mailer
     * @var type object
     */
    private $mailer;
	public function __construct ($db) {
		parent::__construct ($db);	
        require_once (BASEPATH.'protected/lib/PHPMailer/PHPMailerAutoload.php');
        $this->mailer = new PHPMailer();
	}   
    public function send() {
        $this->mailer->IsSMTP(); // enable SMTP
        $this->mailer->SMTPDebug = 3;  // debugging: 1 = errors and messages, 2 = messages only
        $this->mailer->SMTPAuth = true;  // authentication enabled        
        $this->mailer->Host = 'senayang.yacanet.com';
        $this->mailer->Port = 25; 
        $this->mailer->Username = 'dont-reply@yacanet.web.id';  
        $this->mailer->Password = '!@#yaca123';           
        $this->mailer->SetFrom('support@yacanet.com', 'Departement IT STISIPOL Raja Haji');
        $this->mailer->Subject = 'Reset Password';
        $this->mailer->Body = 'Password Anda telah kami reset';
        $this->mailer->AddAddress('rizki@sttindonesia.ac.id');
        if(!$this->mailer->Send()) {
            $error = 'Mail error: '.$this->mailer->ErrorInfo; 
            
        } else {
            $error = 'Message sent!';
            
        }
        echo $error;
    }
}
?>
