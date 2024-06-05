<?php

namespace Tualo\Office\Mail\Routes;

use Tualo\Office\Mail\OutgoingMail;
use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\DS\DSModel;
use PhpImap;


class ListRoute implements IRoute
{

    public static function register()
    {
        BasicRoute::add('/mail/list/(?P<id>\w*)', function ($matches) {
            $db = App::get('session')->getDB();
            // $id = intval(str_replace('/', '', $matches['id']));
            phpinfo( ); exit();

            try {

               //App::showDebug(true);


                /*
                $mailModel = new DSModel('outgoing_mails');

                $mailModel->set('send_from', 'no-reply@tualo.de')
                    ->set('send_from_name', 'Max Muster')
                    ->set('send_to', 'thomas.hoffmann@tualo.de')
                    ->set('reply_to', 'thomas.hoffmann@tualo.de')
                    ->set('reply_to_name', 'Thomas Hoffmann')
                    ->set('subject', 'TEST Subject')
                    ->set('attachment_file', '')
                    ->set('body', 'TEST body');



                $mail = new OutgoingMail($db);
                $res = $mail->add($mailModel);

                $mail->send();
                */
                
                /*
                $mailbox = new PhpImap\Mailbox(
                    '{imap.gmail.com:993/imap/ssl}INBOX', // IMAP server and mailbox folder
                    'some@gmail.com', // Username for the before configured mailbox
                    '*********', // Password for the before configured username
                    App::get('tempPath'), // Directory, where attachments will be saved (optional)
                    'UTF-8', // Server encoding (optional)
                    true, // Trim leading/ending whitespaces of IMAP path (optional)
                    false // Attachment filename mode (optional; false = random filename; true = original filename)
                );
                */
                $config =\Tualo\Office\DS\DSTable::instance('mail_config');
                $config->f('id','=',$matches['id']);
                $configdata = $config->read()->get();
                foreach($configdata as $row){
                    $server='{'.$row['smtp_host'].':993/imap/ssl}INBOX';

                    $mailbox = new PhpImap\Mailbox(
                        $server, // IMAP server and mailbox folder
                        $row['smtp_user'], // Username for the before configured mailbox
                        $row['smtp_pass'], // Password for the before configured username
                        App::get('tempPath'), // Directory, where attachments will be saved (optional)
                        'UTF-8', // Server encoding (optional)
                        true, // Trim leading/ending whitespaces of IMAP path (optional)
                        false // Attachment filename mode (optional; false = random filename; true = original filename)
                    );

                    try {
                        $mailsIds = $mailbox->searchMailbox('ALL');

                        App::result('mailsIds', $mailsIds);
                    } catch(PhpImap\Exceptions\ConnectionException $ex) {
                        throw new \Exception($ex->getErrors('all'));
                    }

                }
                


                App::result('data', $configdata);
                App::result('success', true);
            } catch (\Exception $e) {
                App::result('msg', $e->getMessage());
            }

            App::contenttype('application/json');
        }, ['get', 'post'], true);
    }
}
