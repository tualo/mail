<?php
namespace Tualo\Office\Mail;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\DS\DSModel;
use Tualo\Office\DS\DSCreateRoute;
use Tualo\Office\DS\DSReadRoute;
use PHPMailer\PHPMailer\PHPMailer;


class SMTP {
    public static function get(): PHPMailer {
        $db = App::get('session')->getDB();
        $mail = new PHPMailer(true);
        //$mail->Debugoutput="error_log";
        
        // $mail->SMTPDebug =  SMTP::DEBUG_SERVER;
        $mail->CharSet = "utf-8";
    

        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = $db->singleValue('select smtp_host v from mail_config where id ="default" ',[],'v');  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username =$db->singleValue('select smtp_user v from mail_config where id ="default" ',[],'v');;             // SMTP username
        $mail->Password =$db->singleValue('select smtp_pass v from mail_config where id ="default" ',[],'v');;                           // SMTP password
        $secure = $db->singleValue('select smtp_secure v from mail_config where id ="default" ',[],'v');;

        if ($secure==''){
            $mail->SMTPSecure = false;                            // Enable TLS encryption, `ssl` also accepted
        }else{
            $mail->SMTPSecure = $secure;                            // Enable TLS encryption, `ssl` also accepted
        }
        $mail->Port = 587;                                    // TCP port to connect to
    
        if ( $db->singleValue('select smtp_no_autotls v from mail_config where id ="default" ',[],'v')=='1'){
            $mail->SMTPAutoTLS = false;
        }
    
        if ( $db->singleValue('select smtp_no_certcheck v from mail_config where id ="default" ',[],'v')=='1'){
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
        }

        if (App::configuration('mail', 'force_bbc', "")!=''){
            if (App::configuration('mail', 'force_bbc_name', "")!=''){
                $mail->addBCC(App::configuration('mail', 'force_bbc', ""),App::configuration('mail', 'force_bbc_name', ""));
            }
        }
        return $mail;
    }
}