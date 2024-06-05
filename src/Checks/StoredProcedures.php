<?php

namespace Tualo\Office\Mail\Checks;

use Tualo\Office\Basic\Middleware\Session;
use Tualo\Office\Basic\PostCheck;
use Tualo\Office\Basic\TualoApplication as App;


class StoredProcedures extends PostCheck {

    
    public static function test(array $config){
        $clientdb = App::get('clientDB');
        if (is_null($clientdb)) return;
        $def = [
            'sendReportMail'=>'3c1a32b3abe38f0766fc492abdf90e11',
            
        ];
        self::procedureCheck(
            'ds',
            $def,
            "please run the following command: `./tm install-sql-mail --client ".$clientdb->dbname."`",
            "please run the following command: `./tm install-sql-mail --client ".$clientdb->dbname."`"
        );
        
    }
}