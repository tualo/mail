<?php

namespace Tualo\Office\Mail;

use Tualo\Office\Mail\MailInterface;
use Tualo\Office\Basic\TualoApplication as App;
use PHPMailer\PHPMailer\PHPMailer;

class WrappedPHPMailer implements MailInterface
{
    private PHPMailer $mailer;
    public function __construct()
    {
        // Initialize PHPMailer or any other setup if needed
        $db = App::get('session')->getDB();
        $this->mailer = new PHPMailer();
        $this->mailer->CharSet = "utf-8";


        $this->mailer->isSMTP();                                      // Set mailer to use SMTP
        $this->mailer->Host = $db->singleValue('select smtp_host v from mail_config where id ="default" ', [], 'v');  // Specify main and backup SMTP servers
        $this->mailer->SMTPAuth = true;                               // Enable SMTP authentication
        $this->mailer->Username = $db->singleValue('select smtp_user v from mail_config where id ="default" ', [], 'v');;             // SMTP username
        $this->mailer->Password = $db->singleValue('select smtp_pass v from mail_config where id ="default" ', [], 'v');;                           // SMTP password
        $secure = $db->singleValue('select smtp_secure v from mail_config where id ="default" ', [], 'v');;

        if ($secure == '') {
            $this->mailer->SMTPSecure = false;                            // Enable TLS encryption, `ssl` also accepted
        } else {
            $this->mailer->SMTPSecure = $secure;                            // Enable TLS encryption, `ssl` also accepted
        }
        $this->mailer->Port = 587;                                    // TCP port to connect to

        if ($db->singleValue('select smtp_no_autotls v from mail_config where id ="default" ', [], 'v') == '1') {
            $this->mailer->SMTPAutoTLS = false;
        }

        if ($db->singleValue('select smtp_no_certcheck v from mail_config where id ="default" ', [], 'v') == '1') {
            $this->mailer->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
        }

        if (App::configuration('mail', 'force_bbc', "") != '') {
            if (App::configuration('mail', 'force_bbc_name', "") != '') {
                $this->mailer->addBCC(App::configuration('mail', 'force_bbc', ""), App::configuration('mail', 'force_bbc_name', ""));
            }
        }
    }

    public static function get(): WrappedPHPMailer
    {
        return new WrappedPHPMailer();
    }

    public function addBCC($address, $name = '')
    {
        $this->mailer->addBCC($address, $name);
    }

    public function setFrom($address, $name = '', $auto = true)
    {
        $this->mailer->setFrom($address, $name, $auto);
    }

    public function addAddress($address, $name = '')
    {
        if (is_array($address)) {
            foreach ($address as $addr) {
                $this->mailer->addAddress($addr, $name);
            }
        } else {
            $this->mailer->addAddress($address, $name);
        }
    }

    public function addAttachmentData($string, $filename, $encoding = 'base64', $type = '', $disposition = 'attachment')
    {
        $this->mailer->addStringAttachment($string, $filename, $encoding, $type, $disposition);
    }

    public function addAttachment($path, $name = '', $encoding = 'base64', $type = '', $disposition = 'attachment')
    {
        if (is_array($path)) {
            foreach ($path as $p) {
                $this->mailer->addAttachment($p, $name, $encoding, $type, $disposition);
            }
        } else {
            $this->mailer->addAttachment($path, $name, $encoding, $type, $disposition);
        }
        return $this;
    }

    public function addReplyTo($address, $name = '')
    {
        $this->mailer->addReplyTo($address, $name);
    }

    public function isHtml($isHtml = true)
    {
        $this->mailer->isHTML($isHtml);
        return $this;
    }

    public function setSubject($subject)
    {
        $this->mailer->Subject = $subject;
        return $this;
    }

    public function setAlternativeBody($body)
    {
        $this->mailer->AltBody = $body;
        return $this;
    }

    public function setBody($body)
    {
        $this->mailer->Body = $body;
        return $this;
    }

    public function setListUnsubscribePost($value)
    {
        $this->mailer->addCustomHeader('List-Unsubscribe-Post', $value);
        return $this;
    }

    public function send()
    {
        if (!$this->mailer->send()) {
            throw new \Exception('Mailer Error: ' . $this->mailer->ErrorInfo);
        }
        return true;
    }
}
