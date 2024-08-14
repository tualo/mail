<?php

namespace Tualo\Office\Mail;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Mail\SMTP;

class PugMail
{
    public static function send($data)
    {
        if (!isset($data['mailfrom'])) throw new \Exception('Mailfrom not set');
        if (!isset($data['mailto'])) throw new \Exception('Mailto not set');
        if (!isset($data['mailsubject'])) throw new \Exception('Mailsubject not set');
        if (!isset($data['mailbody'])) throw new \Exception('Mailbody not set');


        $mail = SMTP::get();

        $mail->setFrom(App::configuration('mail', 'force_mail_from', $data['mailfrom']));
        $mails = explode(';', App::configuration('mail', 'force_mail_to', $data['mailto']));


        $db = App::get('session')->getDB();

        if (App::configuration('mail', 'force_mail_to', '') != '') {
            try {
                $allowed_to_mails = $db->direct('select mail v from allowed_to_mails where mail = {mail}', [
                    'mail' => $data['mailto']
                ], 'v');
            } catch (\Exception $e) {
                $allowed_to_mails = [];
            }
        }

        if (count($mails) > 0) {
            foreach ($mails as $value) {
                $mail->addAddress($value);
            }
        }

        // $mail->addReplyTo($item->get('reply_to'),$item->get('reply_to_name'));
        if (isset($data['attachments']) && ($data['attachments'] != '')) {
            foreach ($data['attachments'] as $attachment) {
                if (file_exists(App::get("tempPath") . '/' . $attachment))
                    $mail->addAttachment(App::get("tempPath") . '/' . $attachment, $attachment);
            }
        }

        $mail->isHtml(true);
        $mail->Subject = $data['mailsubject'];
        $mail->Body = $data['mailbody'];



        if (!$mail->send()) {
            throw new \Exception($mail->ErrorInfo);
        }

        if (isset($data['mail_record'])) {
            if (isset($data['mail_record']['__sendmail_callback'])) {
                $r = $data;
                $r['mailto'] = implode(';', $mails);
                unset($r['mailbody']); // kann zu json problemen führen
                $db->direct('set @r = {r}', [
                    'r' => json_encode($r)
                ]);
                $db->direct('call `' . $data['mail_record']['__sendmail_callback'] . '`(@r)');
            }
        }
    }
}
