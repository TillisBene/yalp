<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

class MailHandler {
    private $mailer;
    private $senderEmail;
    private $senderName;

    public function __construct($senderEmail, $senderName) {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 3));
        $dotenv->load();

        $this->mailer = new PHPMailer(true);
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;

        //$this->mailer->isSMTP();
        $this->mailer->isMail();
        $this->mailer->Host = $_ENV['SMTP_HOST'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $_ENV['SMTP_USERNAME'];
        $this->mailer->Password = $_ENV['SMTP_PASSWORD'];
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $_ENV['SMTP_PORT'];
    }

    public function sendMail($recipient, $subject, $content) {
        try {
            $this->mailer->setFrom($this->senderEmail, $this->senderName);
            $this->mailer->addAddress($recipient);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $content;

            return $this->mailer->send();
        } catch (Exception $e) {
            throw new Exception("Mail could not be sent. Mailer Error: {$this->mailer->ErrorInfo}");
        }
    }
}