<?php namespace App\Traits;
use GuzzleHttp\Client;

trait Recaptcha
{
    public function checkRecaptcha($token)
    {
        $client = new Client(['http_errors' => false, 'verify' => false]);
        $response = $client->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'secret' => env('GOOGLE_RECAPTCHA_SECRET'),
                'response' => $token,
                'remoteip' => $_SERVER['REMOTE_ADDR'],
            ],
        ]);
        $body = $response->getBody();
        return json_decode($body);
    }

}
