<?php

namespace Tualo\Office\Mail\Commandline;

use Tualo\Office\Basic\ICommandline;
use Garden\Cli\Cli;
use Garden\Cli\Args;
use Tualo\Office\Basic\TualoApplication as App;


class AsyncSend  implements ICommandline
{
    public static function getCommandName(): string
    {
        return 'mail-async-send';
    }
    public static function setup(Cli $cli)
    {
        $cli->command(static::getCommandName())
            ->description('Send all queued mails')
            ->opt('client', 'only use this client', true, 'string');
    }
    public static function run(Args $args)
    {
        $clientName = $args->getOpt('client');
        App::run();
        $session = App::get('session');
        $sessiondb = $session->db;
        $dbs = $sessiondb->direct('select username db_user, password db_pass, id db_name, host db_host, port db_port from macc_clients ');
        foreach ($dbs as $db) {
            if (($clientName != '') && ($clientName != $db['db_name'])) {
                continue;
            } else {

                App::set('clientDB', $session->newDBByRow($db));
                //PostCheck::formatPrint(['blue'], $msg . '(' . $db['db_name'] . '):  ');
                \Tualo\Office\Mail\AsyncSend::$db = App::get('clientDB');
                \Tualo\Office\Mail\AsyncSend::init();
                \Tualo\Office\Mail\AsyncSend::send();
                //PostCheck::formatPrintLn(['green'], "\t" . ' done');
                App::get('clientDB')->close();
            }
        }
    }
}
