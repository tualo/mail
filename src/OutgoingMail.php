<?php
namespace Tualo\Office\Mail;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\DS\DSModel;
use Tualo\Office\DS\DSCreateRoute;
use Tualo\Office\DS\DSReadRoute;
use PHPMailer\PHPMailer\PHPMailer;


class OutgoingMail{
        private $db;
        private $last_record;
        private $setup=array();

        function __construct($db) {
            $this->db = $db;
        }

        protected function getCMPSetup($cmp,$key){
            if (isset($this->setup[$cmp.'__'.$key])) return $this->setup[$cmp.'__'.$key];
            $v = $this->db->singleValue('select getSetup({cmp},{key}) v',array('cmp'=>$cmp,'key'=>$key),'v');
            $this->setup[$cmp.'__'.$key] = $v;
            return $this->setup[$cmp.'__'.$key];
        }

        
        function add(DSModel $model){
            $request =  $model->toArray();
            $result = DSCreateRoute::createRequest($this->db,'outgoing_mails',$request);
            if (
                isset($result['data']) && 
                isset($result['data'][0]) && 
                isset($result['data'][0]['outgoing_mails__id']) 
            ){

                $this->last_record = $result['data'][0];

            }else{

                throw new \Exception('Could not store that mail');
            }
            return $this;
        }

        function send($id=''){

            $list = array();
            if (($id=='') && (isset($this->last_record) && (isset($this->last_record['outgoing_mails__id']) ))){

                $list= array($this->last_record['outgoing_mails__id']);
            }
            $request = array(
                'limit' => 100,
                'start' => 0,
                'filter' => array(
                    array('property'=>'outgoing_mails__send_date','operator'=>'is null','value'=>'')
                )/*,
                'sort' => array(
                    array('property'=>'create_date','direction'>='asc')
                )
                */
            );
            if (count($list)>0){
                $request['filter'][] = array('property'=>'outgoing_mails__id','operator'=>'in','value'=> $list );
            }

            $list = DSReadRoute::read($this->db,'outgoing_mails',$request);
            foreach ($list['data'] as $item) {

                $this->sendItem($item);
            }

            
        }

        protected function sendItem($item){
            //print_r($item);
            $item = DSModel::fromArray('outgoing_mails',$item);

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
        }

    }   