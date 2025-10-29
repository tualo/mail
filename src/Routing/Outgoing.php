<?php


namespace Tualo\Office\Mail\Routes;

use Tualo\Office\Mail\OutgoingMail;
use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\DS\DSModel;
use PHPMailer\PHPMailer\PHPMailer;

class Outgoing extends \Tualo\Office\Basic\RouteWrapper
{

    public static function register()
    {
        BasicRoute::add('/mail/outgoing', function ($matches) {

            $db = App::get('session')->getDB();
            try {



                $result_list = [];
                $list = $db->direct('select * from outgoing_mails where send_date is null');
                foreach ($list as $item) {

                    $mail = new PHPMailer();

                    $mail->SMTPDebug = 3;                               // Enable verbose debug output
                    $mail->CharSet = "utf-8";

                    $mail->isSMTP();                                      // Set mailer to use SMTP
                    $mail->Host = $db->singleValue('select getSetup("cmp_mail","SMTP_HOST") v', [], 'v');
                    // $this->getCMPSetup('cmp_mail','SMTP_HOST');  // Specify main and backup SMTP servers
                    $mail->SMTPAuth = true;                               // Enable SMTP authentication
                    $mail->Username =  $db->singleValue('select getSetup("cmp_mail","SMTP_USER") v', [], 'v');
                    // $this->getCMPSetup('cmp_mail','SMTP_USER');             // SMTP username
                    $mail->Password =  $db->singleValue('select getSetup("cmp_mail","SMTP_PASS") v', [], 'v');
                    // $this->getCMPSetup('cmp_mail','SMTP_PASS');                           // SMTP password
                    $secure =  $db->singleValue('select getSetup("cmp_mail","SMTP_SECURE") v', [], 'v');
                    // $this->getCMPSetup('cmp_mail','SMTP_SECURE');
                    if ($secure == '') {
                        $mail->SMTPSecure = false;                            // Enable TLS encryption, `ssl` also accepted
                    } else {
                        $mail->SMTPSecure = $secure;                            // Enable TLS encryption, `ssl` also accepted
                    }
                    $mail->Port = 587;                                    // TCP port to connect to

                    if ($db->singleValue('select getSetup("cmp_mail","SMTP_SECURE") v', [], 'v') == '1') {
                        //($this->getCMPSetup('cmp_mail','SMTP_NO_AUTOTLS')=='1'){
                        $mail->SMTPAutoTLS = false;
                    }

                    if ($db->singleValue('select getSetup("cmp_mail","SMTP_NO_CERT_CHECK") v', [], 'v') == '1') {
                        //if ($this->getCMPSetup('cmp_mail','SMTP_NO_CERT_CHECK')=='1'){
                        $mail->SMTPOptions = array(
                            'ssl' => array(
                                'verify_peer' => false,
                                'verify_peer_name' => false,
                                'allow_self_signed' => true
                            )
                        );
                    }



                    $mail->setFrom($item['send_from'], $item['send_from_name']);
                    $mails = explode(';', $item['send_to']);
                    if (count($mails) > 0) {
                        foreach ($mails as $value) {
                            $mail->addAddress($value);
                        }
                    }
                    $mail->addReplyTo($item['reply_to'], $item['reply_to_name']);
                    if ($item['attachment_file'] != '') {
                        parse_str($item['attachment_file'], $output);
                        if (isset($output['cmp'])) {

                            foreach ($output as $key => $value) {
                                if ($key != 'sid') {
                                    $_REQUEST[$key] = $value;
                                }
                            }

                            /*
                            ob_start();
                            include App::get('basePath') . '/cmp/' .$output['cmp'].'/'.$output['cmp'].'.php';
                            $json = json_decode( ob_get_contents(), true );
                            ob_end_clean();
        
                            if (!is_null($json)){
                                if (isset($json['file'])){
                                    $name = basename($json['file']);
                                    $mail->addAttachment( App::get('tempPath') . '/'.$json['file'],$name);
                                }
                            }
                            */
                        }
                    }

                    $mail->Subject = $item['subject'];
                    $mail->Body    = $item['body'];

                    if (!$mail->send()) {
                        $result_list[] = 'Mailer Error: ' . $mail->ErrorInfo;
                    } else {
                        $result_list[] = 'Message has been sent';
                        $sql = 'update outgoing_mails set  send_date=now() where id={id}';
                        $db->direct($sql, $item);
                    }
                }

                App::result('data', $result_list);
                App::result('success', true);
            } catch (\Exception $e) {
                App::result('msg', $e->getMessage());
            }

            App::contenttype('application/json');
        }, ['get', 'post'], true);
    }
}
