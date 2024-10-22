<?php namespace App\Traits;

use Ghasedak\Laravel\GhasedakFacade;

trait Notification
{
    public function send_sms($message, $receptor)
    {
        /* $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.ghasedak.me/v2/sms/send/simple",
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "message=" . $message . "&linenumber=5000270&receptor=" . $receptor . "",
            CURLOPT_HTTPHEADER => array(
                "apikey: 12b9fa2317225495f4bde27e9ef56eebf327adb2dcb55372dae96cd4d9f667c7",
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded",
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            dd($err);
            //echo "cURL Error #:" . $err;
        } else {
            dd($response);
            //echo $response;
        }  */
    
        //$response = GhasedakFacade::SendSimple($receptor, $message, env('GHASEDAKAPI_DEFAULT_LINENUMBER'));
        $response = GhasedakFacade::SendSimple($receptor, $message);
    }


    public function send_otp($param1, $receptor, $template)
    {
        $type = GhasedakFacade::VERIFY_MESSAGE_TEXT;
        $response = GhasedakFacade::setVerifyType($type)->Verify($receptor, $template, $param1);
    }


}
