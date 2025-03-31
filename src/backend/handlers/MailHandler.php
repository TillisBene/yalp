<?php

namespace handlers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use Dotenv\Dotenv;

class MailHandler {
    private $mailer;

    public function __construct() {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 3));
        $dotenv->load();

        // Validate required environment variables
        $required_vars = ['SMTP_HOST', 'SMTP_USERNAME', 'SMTP_PASSWORD', 'SMTP_PORT', 'MAIL_FROM_ADDRESS', 'MAIL_FROM_NAME'];
        foreach ($required_vars as $var) {
            if (!isset($_ENV[$var])) {
                throw new Exception("Missing required environment variable: {$var}");
            }
        }

        $this->mailer = new PHPMailer(true);

        // Enable debugging
        $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
        $this->mailer->Debugoutput = function($str, $level) {
            error_log("PHPMailer Debug: $str");
        };

        // Configure SMTP
        $this->mailer->isSMTP();
        $this->mailer->Host = $_ENV['SMTP_HOST'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $_ENV['SMTP_USERNAME'];
        $this->mailer->Password = $_ENV['SMTP_PASSWORD'];
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mailer->Port = (int)$_ENV['SMTP_PORT'];

        // Set default sender from environment
        $this->mailer->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
    }

    public function sendMail($recipient, $subject, $content) {
        try {
            // Clear previous recipients
            $this->mailer->clearAddresses();
            
            $this->mailer->addAddress($recipient);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $content;
            $this->mailer->AltBody = strip_tags($content); // Plain text version

            $result = $this->mailer->send();
            error_log("Email sent successfully to: $recipient");
            return $result;
        } catch (Exception $e) {
            error_log("Mail Error: {$this->mailer->ErrorInfo}");
            error_log("Stack trace: " . $e->getTraceAsString());
            throw new Exception("Mail could not be sent. Mailer Error: {$this->mailer->ErrorInfo}", 0, $e);
        }
    }
}