<?php

namespace Tualo\Office\Mail\Checks;

use Tualo\Office\Basic\Middleware\Session;
use Tualo\Office\Basic\PostCheck;
use Tualo\Office\Basic\TualoApplication as App;


class StoredProcedures extends PostCheck
{


    public static function test(array $config)
    {
        $clientdb = App::get('clientDB');
        if (is_null($clientdb)) return;
        $def = [
            'sendReportMail' => '99517983dcc32b1da94c837ee3b4a3f3',

        ];
        self::procedureCheck(
            'ds',
            $def,
            "please run the following command: `./tm install-sql-mail --client " . $clientdb->dbname . "`",
            "please run the following command: `./tm install-sql-mail --client " . $clientdb->dbname . "`"
        );
    }
}
