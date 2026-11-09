<?php
/**
 * DonutsTecLiberia — Mail Configuration
 *
 * Set MAIL_ENABLED = true once you fill in real SMTP credentials.
 * While it's false, emails are logged to /logs/mail.log instead of being sent.
 */
return [
    'enabled'   => false,        // ← set true when SMTP is configured
    'host'      => 'smtp.gmail.com',
    'port'      => 587,
    'username'  => 'donutstec@gmail.com',
    'password'  => 'sjlw pfjq viqx hzph',
    'encryption'=> 'tls',        // 'tls' or 'ssl'
    'from'      => 'noreply@donutstec.com',
    'from_name' => 'DonutsTecLiberia',
    'reply_to'  => 'info@donutstec.com',
];