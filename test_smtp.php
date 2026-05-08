<?php
require 'c:\xampp\htdocs\SportFuel-Module1\controller\smtp_settings.php';
require 'c:\xampp\htdocs\SportFuel-Module1\controller\api.php';

$settings = [
    'host' => $SMTP_HOST,
    'port' => $SMTP_PORT,
    'username' => $SMTP_USERNAME,
    'password' => $SMTP_PASSWORD,
    'from_email' => $SMTP_FROM_EMAIL,
    'from_name' => $SMTP_FROM_NAME
];

list($sent, $err) = smtpSendMail('artengododingo@gmail.com', 'Test', 'Test', $settings);
echo "\n==== RESULTAT SMTP ====\n";
echo "Sent: " . ($sent ? 'TRUE' : 'FALSE') . "\n";
echo "Error: " . $err . "\n";
echo "=======================\n";
