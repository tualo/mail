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
use PHPMailer\PHPMailer\PHPMailer;

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
                unlink($res['filename']);

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

            $mail = new PHPMailer(true);
            $mail->Debugoutput="error_log";
            
            $mail->SMTPDebug =  SMTP::DEBUG_SERVER;
            $mail->CharSet = "utf-8";
        
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = $this->getCMPSetup('cmp_mail','SMTP_HOST');  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = $this->getCMPSetup('cmp_mail','SMTP_USER');             // SMTP username
            $mail->Password = $this->getCMPSetup('cmp_mail','SMTP_PASS');                           // SMTP password
            $secure = $this->getCMPSetup('cmp_mail','SMTP_SECURE');

            if ($secure==''){
                $mail->SMTPSecure = false;                            // Enable TLS encryption, `ssl` also accepted
            }else{
                $mail->SMTPSecure = $secure;                            // Enable TLS encryption, `ssl` also accepted
            }
            $mail->Port = 587;                                    // TCP port to connect to
        
            if ($this->getCMPSetup('cmp_mail','SMTP_NO_AUTOTLS')=='1'){
                $mail->SMTPAutoTLS = false;
            }
        
            if ($this->getCMPSetup('cmp_mail','SMTP_NO_CERT_CHECK')=='1'){
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );
            }
        
            
            $mail->setFrom($item->get('send_from'),$item->get('send_from_name'));
            $mails = explode(';',$item->get('send_to'));
            if (count($mails)>0){
                foreach ($mails as $value) {
                    $mail->addAddress($value);
                }
            }
        
            $mail->addReplyTo($item->get('reply_to'),$item->get('reply_to_name'));
        
            if ($item->get('attachment_file')!=''){
                //echo $item->get('attachment_file'); exit();
                if (file_exists( App::get("tempPath").'/'.$item->get('attachment_file') )){
                    $name = basename($item->get('attachment_file'));
                    $mail->addAttachment( App::get("tempPath").'/'.$item->get('attachment_file') ,$name);
                }else{
                    parse_str($item->get('attachment_file'), $output);
                    if (isset($output['cmp'])){
                        foreach ($output as $key => $value) {
                            if ($key!='sid'){
                                $_REQUEST[$key]=$value;
                            }
                        }
                
                        ob_start();
                            include App::get("basePath") . '/cmp/' .$output['cmp'].'/'.$output['cmp'].'.php';
                            $json = json_decode( ob_get_contents(), true );
                        ob_end_clean();
                
                        if (!is_null($json)){
                            if (isset($json['file'])){
                                $name = basename($json['file']);
                                $mail->addAttachment( App::get("tempPath") . '/'.$json['file'],$name);
                            }
                        }
                
                    }
                }
            }
        
            $mail->Subject = $item->get('subject');
            $mail->Body    = $item->get('body');
        
            if(!$mail->send()) {
                throw new \Exception($mail->ErrorInfo);
            } else {
                $this->db->execute_with_hash('update outgoing_mails set  send_date=now() where id={id}',['id'=>$item->get('id')]);
            }
            
            $mail = new PHPMailer();
            $mail->CharSet = "UTF-8";
            $mail->setFrom("john@example.com", "John Doe");
            $mail->addAddress("jane@example.com", "Jane Doe");
            $mail->Subject = "Subject";

            $mail->isHtml(true);
            $mail->AddEmbeddedImage('top.jpg', 'TBP', 'top.jpg');
            $mail->Body = $html;
            //$mail->AltBody = "";

            if ($mail->Send()) {
            echo 'OK';
            }
            else {
            echo 'Error!' . $mail->ErrorInfo;
            }
        }, ['put'], true);
    }
}
