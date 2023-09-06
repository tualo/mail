<?php

namespace Tualo\Office\Mail\Routes;

use Tualo\Office\Mail\OutgoingMail;
use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\DS\DSModel;

class Send implements IRoute
{

    public static function register()
    {
        BasicRoute::add('/mail/renderpug/(?P<pug_template>\w+)', function ($matches) {
            $db = App::get('session')->getDB();
            

            try {
                $postdata = json_decode(file_get_contents("php://input"),true);
                if(is_null($postdata)) throw new \Exception('Payload not readable');

                    App::result('data', $postdata);
                    App::result('success', true);
                } catch (\Exception $e) {
                    App::result('msg', $e->getMessage());
                }
                App::contenttype('application/json');
        }, ['put'], true);

        BasicRoute::add('/mail/send(?P<id>(\/\d*)*)', function ($matches) {
            $db = App::get('session')->getDB();
            $id = intval(str_replace('/', '', $matches['id']));

            try {

                App::showDebug(true);


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


                App::result('data', $id);
                App::result('success', true);
            } catch (\Exception $e) {
                App::result('msg', $e->getMessage());
            }

            App::contenttype('application/json');
        }, ['get', 'post'], true);
    }
}
