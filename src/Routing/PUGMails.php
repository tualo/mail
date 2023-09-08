<?php


namespace Tualo\Office\Mail\Routes;

use Tualo\Office\Mail\OutgoingMail;
use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\DS\DSTable;
use Tualo\Office\PUG\PUG;
use Tualo\Office\RemoteBrowser\RemotePDF;
use DOMDocument;
use Tualo\Office\Mail\SMTP;
class PUGMails implements IRoute{
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
                $attachment_ids = [];
                if (isset($postdata['__pug_attachments'])){
                    $res = RemotePDF::get($postdata['__table_name'],$postdata['__pug_attachments'],$postdata['__id'],true);
                    if (isset($res['filename'])){
                        $attachments[] = [
                            'filename'=>basename($res['filename']),
                            'title'=>$res['title'],
                            'contenttype'=>$res['contenttype'],
                            'filesize'=>$res['filesize'],
                        ];
                        $attachment_ids[] = basename($res['filename']);
                    }
                }
                // unlink($res['filename']);

                App::result('postdata', $postdata);
                App::result('attachments', $attachments);
                App::result('data', [
                    'mailfrom'=>$db->singleValue('select getSessionUser() v',[],'v'),
                    'mailsubject'=>$subject,
                    'mailto'=>$info['mail_addresses'][0],
                    'mailbody' => $html,
                    'attachments' => $attachment_ids,
                ]);

                App::result('html', $html);
                App::result('success', true);
            } catch (\Exception $e) {
                App::result('msg', $e->getMessage());
            }
            App::contenttype('application/json');
        }, ['PUT','post'], true);

        BasicRoute::add('/mail/sendpug', function ($matches) {
            $data = json_decode(file_get_contents("php://input"),true);
            if(is_null($data)) throw new \Exception('Payload not readable');
            
            $mail =SMTP::get();
         
            $mail->setFrom($data['mailfrom']);
            $mails = [App::configuration('mail','force_mail_to',$data['mailto'])];
            
            
            if (count($mails)>0){
                foreach ($mails as $value) {
                    $mail->addAddress($value);
                }
            }
        
            // $mail->addReplyTo($item->get('reply_to'),$item->get('reply_to_name'));
            foreach($data['attachments'] as $attachment){
                if(file_exists(App::get("tempPath").'/'.$attachment))
                $mail->addAttachment( App::get("tempPath").'/'.$attachment,$attachment);
            }
            
            $mail->isHtml(true);
            $mail->Subject = $data['mailsubject'];
            $mail->Body    = $data['mailbody'];
        
            print_r($mails); exit();

            if(!$mail->send()) {
                throw new \Exception($mail->ErrorInfo);
            }
            
        }, ['put'], true);
    }
}
