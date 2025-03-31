<?php
require_once('vendor/autoload.php');

use handlers\MailHandler;

try {
    $mailHandler = new MailHandler();
    $result = $mailHandler->sendMail('tillbene@gmail.com', 'Test', 'This is a test email');
    echo "Email sent successfully!\n";
    var_dump($result);
} catch (\Exception $e) {
    echo "Failed to send email:\n";
    echo "Error message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}