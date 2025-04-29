<?php

namespace Tualo\Office\Mail\CMSMiddleware;

use Tualo\Office\Mail\SMTP as SMTP;

class Mail
{

    public static function run(&$request, &$result)
    {
        $result['mail'] = SMTP::get();
    }
}
