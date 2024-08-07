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
use Tualo\Office\Mail\MailerHTML;

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

                if (!isset($postdata['__sendmail_filterfields'])){
                    $f=[];
                    foreach($postdata as $key => $value) $f[] = $key;
                    $postdata['__sendmail_filterfields'] = implode(',',$f);
                }
                $postdata['__sendmail_filterfields'] = explode(',',$postdata['__sendmail_filterfields']);
                foreach($postdata as $key => $value) {
                    if (in_array($key,$postdata['__sendmail_filterfields']))
                    $infotable->filter($key,'=',$value);
                }

                    
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
                if (isset($postdata['__pug_attachments']) && $postdata['__pug_attachments']!=''){

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


                if (isset($postdata['__ds_files_attachments'])){
                    if (is_string($postdata['__ds_files_attachments'])){
                        $postdata['__ds_files_attachments'] = json_decode($postdata['__ds_files_attachments'],true);
                    }

                    foreach($postdata['__ds_files_attachments'] as $file_id){
                        $sql = 'select 
                        ds_files.file_id,
                        ds_files.name,
                        ds_files_data.data
                        from ds_files
                        join ds_files_data
                        on ds_files.file_id = ds_files_data.file_id';
                        $res = $db->singleRow($sql,['file_id'=>$file_id]);
                        if (isset($res['data'])){
                            list($mime,$data) = explode(',',$res['data']);
                            $attachments[] = [
                                'filename'=>$res['name'],
                                'title'=>$res['name'],
                                'contenttype'=>$mime,
                                'filesize'=>strlen($data),
                            ];
                            $attachment_ids[] = $res['file_id'];
                            file_put_contents(App::get("tempPath").'/'.$res['file_id'],base64_decode($data));
                        }


                    }

                }
                // unlink($res['filename']);
                if (isset($info['_hint_message'])){
                    App::result('_hint_message', $info['_hint_message']);
                }
                if (isset($info['_form_editable'])){
                    App::result('_form_editable', $info['_form_editable']==1);
                }
                if (isset($info['_form_hide_from'])){
                    App::result('_form_hide_from', $info['_form_hide_from']==1);
                }
                if (isset($info['_form_hide_attachments'])){
                    App::result('_form_hide_attachments', $info['_form_hide_attachments']==1);
                }
               
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
        }, ['put'], true);

        BasicRoute::add('/mail/sendpug', function ($matches) {
            App::contenttype('application/json');
            try{
                $db = App::get('session')->getDB();
                $data = json_decode(file_get_contents("php://input"),true);
                if(is_null($data)) throw new \Exception('Payload not readable');
                if(!isset($data['mailfrom'])) throw new \Exception('Mailfrom not set');
                if(!isset($data['mailto'])) throw new \Exception('Mailto not set');
                if(!isset($data['mailsubject'])) throw new \Exception('Mailsubject not set');
                if(!isset($data['mailbody'])) throw new \Exception('Mailbody not set');

                
                $mail =SMTP::get();
            
                $mail->setFrom(App::configuration('mail','force_mail_from',$data['mailfrom']));
                $mails = explode(';',App::configuration('mail','force_mail_to',$data['mailto']));


                $db = App::get('session')->getDB();

                    if (App::configuration('mail','force_mail_to','')!=''){
                        try{
                            $allowed_to_mails = $db->direct('select mail v from allowed_to_mails where mail = {mail}',[
                            'mail'=>$data['mailto']
                        ],'v');
                    }catch(\Exception $e){
                        $allowed_to_mails = [];
                    }
                }

                if (count($mails)>0){
                    foreach ($mails as $value) {
                        $mail->addAddress($value);
                    }
                }
            
                // $mail->addReplyTo($item->get('reply_to'),$item->get('reply_to_name'));
                if (isset($data['attachments'])&&($data['attachments']!='')){
                    foreach($data['attachments'] as $attachment){
                        if(file_exists(App::get("tempPath").'/'.$attachment))
                        $mail->addAttachment( App::get("tempPath").'/'.$attachment,$attachment);
                    }
                }
                
                $mail->isHtml(true);
                $mail->Subject = $data['mailsubject'];
                $mail->Body = $data['mailbody'];

                /*
                $resMailerHTML = MailerHTML::htmlImagesToCID($data['mailbody'],App::get("tempPath"));
                $mail->Body = $data['mailbody'] $resMailerHTML['html'];
                foreach($resMailerHTML['cids'] as $cid){
                    $mail->AddEmbeddedImage($cid['file'], $cid['cid']);
                }
                */
            
                
                if(!$mail->send()) {
                    throw new \Exception($mail->ErrorInfo);
                }

                if(isset($data['mail_record'])){
                    if(isset($data['mail_record']['__sendmail_callback'])){
                        $r = $data;
                        $r['mailto'] = implode(';',$mails);
                        unset($r['mailbody']); // kann zu json problemen führen
                        $db->direct('set @r = {r}',[
                            'r'=>json_encode($r)
                        ]);
                        $db->direct('call `'.$data['mail_record']['__sendmail_callback'].'`(@r)');
                    }
                }

                App::result('success', true);
            } catch (\Exception $e) {
                App::contenttype('application/json');
                App::result('msg', $e->getMessage());
            }
        }, ['put','post'], true);
    }
}
