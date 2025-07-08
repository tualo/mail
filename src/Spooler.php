<?php

namespace Tualo\Office\Mail;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\DS\DSModel;
use Tualo\Office\DS\DSCreateRoute;
use Tualo\Office\DS\DSReadRoute;
use PHPMailer\PHPMailer\PHPMailer;

class Spooler
{
    public static $messages = [];
    public static $config = [];
    public static $initialised = false;



    public static function init(bool $sendOnShutdown = true)
    {
        if (self::$initialised) return;
        $db = App::get('session')->getDB();
        self::$config = [
            'host' => $db->singleValue('select smtp_host v from mail_config where id ="default" ', [], 'v'),
            'username' => $db->singleValue('select smtp_user v from mail_config where id ="default" ', [], 'v'),
            'password' => $db->singleValue('select smtp_pass v from mail_config where id ="default" ', [], 'v'),
            'secure' => $db->singleValue('select smtp_secure v from mail_config where id ="default" ', [], 'v'),
            'smtp_no_autotls' => $db->singleValue('select smtp_no_autotls v from mail_config where id ="default" ', [], 'v'),
            'smtp_no_certcheck' => $db->singleValue('select smtp_no_certcheck v from mail_config where id ="default" ', [], 'v')
        ];
        if (is_null(self::$config['host']) || self::$config['host'] == '' || self::$config['username'] == '' || self::$config['password'] == '') {
            throw new \Exception('Mail configuration not set');
        }
        if ($sendOnShutdown) {
            // Register the send function to be called on shutdown
            register_shutdown_function([self::class, 'send']);
        }
        //register_shutdown_function('\Tualo\Office\Mail\Spooler::send');
        self::$initialised = true;
    }

    public static function addMail($subject, $from, $to, $message, $attachments = [])
    {
        self::init();
        self::$messages[] = ['subject' => $subject, 'from' => $from, 'to' => $to, 'message' => $message, 'attachments' => $attachments];
    }

    public static function send()
    {
        try {
            foreach (self::$messages as $message) {

                $mail = new PHPMailer(true);
                //$mail->Debugoutput="error_log";

                // $mail->SMTPDebug =  SMTP::DEBUG_SERVER;
                $mail->CharSet = "utf-8";


                $mail->isSMTP();                                      // Set mailer to use SMTP
                $mail->Host = self::$config['host'];  // Specify main and backup SMTP servers
                $mail->SMTPAuth = true;                               // Enable SMTP authentication
                $mail->Username = self::$config['username'];             // SMTP username
                $mail->Password =  self::$config['password'];                        // SMTP password
                $secure = self::$config['secure'];

                if ($secure == '') {
                    $mail->SMTPSecure = false;                            // Enable TLS encryption, `ssl` also accepted
                } else {
                    $mail->SMTPSecure = $secure;                            // Enable TLS encryption, `ssl` also accepted
                }
                $mail->Port = 587;                                    // TCP port to connect to

                if (self::$config['smtp_no_autotls'] == '1') {
                    $mail->SMTPAutoTLS = false;
                }

                if (self::$config['smtp_no_certcheck'] == '1') {
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );
                }

                $mail->setFrom($message['from']);
                $mail->addAddress($message['to']);
                $mail->Subject = $message['subject'];
                $mail->isHTML(false);
                $mail->Body    = $message['message'];

                foreach ($message['attachments'] as $attachment) {
                    $mail->addAttachment($attachment['path'], $attachment['name']);
                }

                if (!$mail->send()) {
                    //echo "<b> FEHLER</b><br/>".PHP_EOL;
                } else {
                }
            }
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            //echo "<b> FEHLER</b><br/>".PHP_EOL;
        }
    }
}
