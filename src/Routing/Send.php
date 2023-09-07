<?php

namespace Tualo\Office\Mail\Routes;

use Tualo\Office\Mail\OutgoingMail;
use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\DS\DSModel;
use Tualo\Office\DS\DSTable;
use Tualo\Office\PUG\PUG;
use Tualo\Office\RemoteBrowser\RemotePDF;
use DOMDocument;

class Send implements IRoute
{

    public static function register()
    {
        BasicRoute::add('/mail/renderpug', function ($matches) {
            $db = App::get('session')->getDB();
            
            /*
            CREATE OR REPLACE VIEW `view_blg_list_angebot_mailinfo` as
            select h.id,json_arrayagg(a.email) mail_addresses from 
            -- 
            blg_hdr_angebot h 
            join blg_adressen_angebot b on h.id = b.id 
            join adressen a on (a.kundennummer,a.kostenstelle) = (b.kundennummer,b.kostenstelle) 
            and a.email<>''
            group by h.id
            */
            try {

                App::set("pugCachePath", App::get("basePath").'/cache/'.$db->dbname.'/cache' );

                $postdata = json_decode(file_get_contents("php://input"),true);
                if(is_null($postdata)) throw new \Exception('Payload not readable');
                
                if (!isset($postdata['__sendmail_template'])) throw new \Exception('Template not set');
                if (!isset($postdata['__sendmail_info'])) throw new \Exception('Info not set');
                $template=$postdata['__sendmail_template'];
                

                $infotable = new DSTable($db,$postdata['__sendmail_info']);
                foreach($postdata as $key => $value) $infotable->filter($key,'=',$value);
                    
                $infotable->limit(1)->read();
                if ($infotable->empty()) throw new \Exception('Info not found');
                $info = $infotable->getSingle();
                $info['mail_addresses']=json_decode($info['mail_addresses'],true);
                App::result('info', $info);
                PUG::exportPUG($db);

                $html = PUG::render($template,$postdata);

                $subject = '';
                $dom = new DOMDocument();

                if($dom->loadHTML($html)) {
                    $list = $dom->getElementsByTagName("title");
                    if ($list->length > 0) {
                        $subject = $list->item(0)->textContent;
                    }
                }
                $attachments=[];
                if (isset($postdata['__pug_attachments'])){
                    $res = RemotePDF::get($postdata['__table_name'],$postdata['__pug_attachments'],$postdata['__id'],true);
                    if (isset($res['filename'])){
                        $attachments[] = [
                            'filename'=>basename($res['filename']),
                            'title'=>$res['title'],
                            'contenttype'=>$res['contenttype'],
                            'filesize'=>$res['filesize'],
                        ];
                    }
                }
                unlink($res['filename']);

                App::result('postdata', $postdata);
                App::result('data', [
                    'mailsubject'=>$subject,
                    'mailto'=>$info['mail_addresses'][0],
                    'mailbody' => $html,
                    'attachments' => $attachments,
                ]);

                App::result('html', $html);
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
