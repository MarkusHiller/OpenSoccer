<?php

class EmailService {
    
    public static function send($email, $username, $password) {
        echo 'EMAILSERVICE';
		$empfaenger = $email;
        $betreff = CONFIG_SITE_NAME.': Willkommen';
        $nachricht = "Hallo ".$username.",\n\nDu hast Dich erfolgreich auf ".CONFIG_SITE_DOMAIN." registriert. Bitte logge Dich jetzt mit Deinen Benutzerdaten ein, um Deinen Account zu aktivieren. Und dann kann es auch schon losgehen ...\n\nDamit Du Dich anmelden kannst, findest Du hier noch einmal Deine Benutzerdaten:\n\nE-Mail: ".$email."\nBenutzername: ".$username."\nPasswort: ".$password."\n\nWir wünschen Dir noch viel Spaß beim Managen!\n\nSportliche Grüße\n".CONFIG_SITE_NAME."\n".CONFIG_SITE_DOMAIN."\n\n------------------------------\n\nDu erhältst diese E-Mail, weil Du Dich auf ".CONFIG_SITE_DOMAIN." mit dieser Adresse registriert hast. Du kannst Deinen Account jederzeit löschen, nachdem Du Dich eingeloggt hast, sodass Du anschließend keine E-Mails mehr von uns bekommst. Bei Missbrauch Deiner E-Mail-Adresse meldest Du Dich bitte per E-Mail unter ".CONFIG_SITE_EMAIL;
        if (CONFIG_EMAIL_PHP_MAILER) {
            require_once(__DIR__.'/../phpmailer/PHPMailerAutoload.php');
            $mail = new PHPMailer(); // create a new object
            $mail->CharSet = CONFIG_EMAIL_CHARSET;
            $mail->IsSMTP();
            $mail->SMTPAuth = CONFIG_EMAIL_AUTH;
            $mail->SMTPSecure = CONFIG_EMAIL_SECURE;
            $mail->Host = CONFIG_EMAIL_HOST;
            $mail->Port = CONFIG_EMAIL_PORT;
            $mail->Username = CONFIG_EMAIL_USER;
            $mail->Password = CONFIG_EMAIL_PASS;
            $mail->SetFrom(CONFIG_EMAIL_FROM, CONFIG_SITE_NAME);
            $mail->Subject = $betreff;
            $mail->Body = $nachricht;
            $mail->AddAddress($empfaenger);
            $mail->Send();
        }
        else{
            $header = "From: ".CONFIG_SITE_NAME." <".CONFIG_SITE_EMAIL.">\r\nContent-type: text/plain; charset=utf-8";
            mail($empfaenger, $betreff, $nachricht, $header);
        }
    }
}