<?php

namespace Tualo\Office\Mail;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Mail\MailInterface as MailInterface;
use Tualo\Office\Mail\WrappedPHPMailer;

use Tualo\Office\DS\DSModel;
use Tualo\Office\DS\DSCreateRoute;
use Tualo\Office\DS\DSReadRoute;


class SMTP
{

    public static function get(): MailInterface
    {
        $usemsgraph = App::configuration('mail', 'use_msgraph', '0');
        if ($usemsgraph == '1') {
            return self::getMSGraph();
        } else {
            return self::getPHPMailer();
        }
    }
    public static function getMSGraph()
    {
        if (class_exists('\Tualo\Office\MicrosoftMail\MSGraphMail')) {
            return \Tualo\Office\MicrosoftMail\MSGraphMail::get();
        } else {
            throw new \Exception('MSGraphMail not found');
        }
        // return \Tualo\Office\MicrosoftMail\MSGraphMail::get();
    }

    public static function getPHPMailer(): WrappedPHPMailer
    {

        $mail = new WrappedPHPMailer();
        //$mail->Debugoutput="error_log";

        // $mail->SMTPDebug =  SMTP::DEBUG_SERVER;

        return $mail;
    }
}
