<?php
$to      = 'gaiapiersanti19@gmail.com';
$subject = 'Prova invio mail';
$message = 'Funziona!';
$headers = 'From: mtauro569@gmail.com' . "\r\n" .
           'Reply-To: mtauro569@gmail.com' . "\r\n";

if (mail($to, $subject, $message, $headers, '-fmtauro569@gmail.com')) {
    echo "Mail inviata con successo!";
} else {
    echo "Errore nell'invio.";
}