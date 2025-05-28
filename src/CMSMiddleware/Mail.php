<?php

namespace Tualo\Office\Mail\CMSMiddleware;

use Tualo\Office\Mail\MailInterface as MailInterface;
use Tualo\Office\Mail\SMTP as SMTP;
use PHPMailer\PHPMailer\PHPMailer;
use Tualo\Office\MicrosoftMail\MSGraphMail;



class Mail
{

    public static function fn(): callable
    {
        return function (array $options = []): MailInterface {
            return SMTP::get();
        };
    }

    public static function run(&$request, &$result)
    {
        $result['mail'] = SMTP::get();
        $result['email'] = self::fn();
    }
}
