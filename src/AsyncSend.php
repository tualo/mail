<?php

namespace Tualo\Office\Mail;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\DS\DSModel;
use Tualo\Office\DS\DSCreateRoute;
use Tualo\Office\DS\DSReadRoute;
use PHPMailer\PHPMailer\PHPMailer;

class AsyncSend
{
    public static $messages = [];
    public static $config = [];
    public static $initialised = false;

    public static $db = null;


    public static function init()
    {
        if (self::$initialised) return;
        if (is_null(self::$db)) {
            self::$db = App::get('session')->getDB();
        }

        $db = self::$db;
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
        self::$initialised = true;
    }

    public static function addMail($subject, $from, $to, $message, $attachments = [])
    {
        register_shutdown_function([self::class, 'runCommand']);

        self::init();
        self::$messages[] = ['subject' => $subject, 'from' => $from, 'to' => $to, 'message' => $message, 'attachments' => $attachments];

        self::$db->direct('insert into mail_async_send (id,maildata) values (uuid(),{maildata})', [
            'maildata' => json_encode(['subject' => $subject, 'from' => $from, 'to' => $to, 'message' => $message, 'attachments' => $attachments])
        ]);
    }

    public static function runCommand()
    {
        exec('nohup ' . escapeshellarg(__DIR__ . '/../../tm mail-async-send --client ' . self::$db->dbname) . ' > /dev/null 2>&1 &');
    }

    public static function send()
    {
        try {
            if (count(self::$messages) == 0) {
                // lade self::$messages
                self::$messages = [];
                $rows = self::$db->direct('select id, maildata from mail_async_send where is_sending is null and send_at is null limit 100');
                foreach ($rows as $row) {
                    $maildata = json_decode($row['maildata'], true);
                    self::$messages[] = $maildata;
                    self::$db->direct('update mail_async_send set is_sending=now() where id={id}', ['id' => $row['id']]);
                }
            }
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
                    self::$db->direct('update mail_async_send set error_message={error_message} where id={id}', ['error_message' => '--_--', 'id' => $row['id']]);
                } else {
                    self::$db->direct('update mail_async_send set send_at=now() where id={id}', ['id' => $row['id']]);
                }
            }
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            self::$db->direct('update mail_async_send set error_message={error_message} where id={id}', ['error_message' => $e->getMessage(), 'id' => $row['id']]);
        }
    }
}
