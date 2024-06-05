<?php
namespace Tualo\Office\Mail;

class MailerHTML {
    public static function htmlImagesToCID($html,$path) {
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $images = $dom->getElementsByTagName('img');
        $cid = [];
        foreach ($images as $image) {
            $src = $image->getAttribute('src');
            // $type = pathinfo($src, PATHINFO_EXTENSION);
            if (strpos($src, 'data:image') !== 0) {
                continue;
            }
            $type = explode(';', explode('/', $src)[1])[0];
            $ext='none';
            switch ($type) {
                case 'jpeg':
                        $ext = 'jpg';
                        break;
                    case 'png':
                        $ext = 'png';
                        break;
                    case 'svg+xml':
                        $ext = 'svg';
                        break;
            }
            $fname = $path . '/' . uniqid() . '.'.$ext;

            switch ($type) {
                case 'jpeg':
                        file_put_contents($fname, base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $src)));
                        break;
                    case 'png':
                        file_put_contents($fname, base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $src)));
                        break;
                    case 'svg+xml':
                        file_put_contents($fname, base64_decode(preg_replace('#^data:image/(\w|\+)+;base64,#i', '', $src)));
                        break;
            }
            
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

            $cid[] = [
                'file'=>$path . '/' . uniqid() . '.'.$ext,
                'cid'=>'cid:image-' . count($cid) 
            ];
            $image->setAttribute('src', 'cid:image-' . count($cid) );
        }
        return [
            'html'=>$dom->saveHTML(), 
            'cids' => $cid
        ];
    }
}
/*
$test = file_get_contents('test.html');
print_r(MailerHTML::htmlImagesToCID($test,'.'));
*/