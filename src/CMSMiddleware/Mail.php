<?php

namespace Tualo\Office\Mail\CMSMiddleware;

use Tualo\Office\Mail\MailInterface as MailInterface;
use Tualo\Office\Mail\SMTP as SMTP;
use \Tualo\Office\Mail\Spooler;
use PHPMailer\PHPMailer\PHPMailer;
use Tualo\Office\MicrosoftMail\MSGraphMail;
use Tualo\Office\Mail\AsyncSend;

class Mail
{

    public static function fn(): callable
    {
        return function (array $options = []): MailInterface {
            return SMTP::get();
        };
    }

    public static function spooler(): callable
    {
        return function ($subject, $to, $from, $body): void {
            Spooler::addMail(
                $subject,
                $to,
                $from,
                $body
            );
        };
    }

    public static function asyncsend(): callable
    {
        return function ($subject, $to, $from, $body): void {
            AsyncSend::addMail(
                $subject,
                $to,
                $from,
                $body
            );
        };
    }




    public static function run(&$request, &$result)
    {
        $result['mail'] = SMTP::get();
        $result['email'] = self::fn();
        $result['spooler'] = self::spooler();
        $result['asyncmail'] = self::asyncsend();
    }
}
