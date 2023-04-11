<?php


namespace Tualo\Office\Mail\Routes;

use Tualo\Office\Mail\OutgoingMail;
use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\DS\DSModel;
use PHPMailer\PHPMailer\PHPMailer;

class MailLink implements IRoute
{

    public static function register()
    {
        BasicRoute::add('/mail/link/(?P<link>\w+)', function ($matches) {

            $db = App::get('session')->getDB();

            try {

                $link_record = $db->singleRow('select * from cmp_mail_proxy_links where linkid={link} and valid_until>=now()', $matches);
                if ($link_record) {
                    $ch = curl_init();
                    //        print_r($link_record);
                    curl_setopt($ch, CURLOPT_URL, $link_record['link']);
                    //curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['USER_AGENT']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                    curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
                    curl_setopt($ch, CURLOPT_HEADER, 1);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

                    // not verified connection !
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);



                    $response = curl_exec($ch);
                    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $curl_getinfo = curl_getinfo($ch);
                    $header_text = substr($response, 0, $curl_getinfo['header_size']);
                    $body = substr($response, $curl_getinfo['header_size']);


                    curl_close($ch);
                    $headers = explode("\n", $header_text);
                    for ($i = 1, $s = count($headers); $i < $s; $i++) {
                        if (
                            (strpos($headers[$i], 'Date:') !== 0) &&
                            (strpos($headers[$i], 'Server:') !== 0) &&
                            (strpos($headers[$i], 'Access-Control-Allow-Headers:') !== 0) &&
                            (strpos($headers[$i], 'Access-Control-Allow-Origin:') !== 0) &&
                            (strpos($headers[$i], 'X-Powered-By:') !== 0) &&
                            (strpos($headers[$i], 'Transfer-Encoding:') !== 0)
                        ) {
                            header($headers[$i]);
                        }
                    }
                    echo $body;
                    exit();
                }
            } catch (\Exception $e) {
                App::result('msg', $e->getMessage());
            }
            App::contenttype('application/json');
        }, ['get', 'post'], true);
    }
}
