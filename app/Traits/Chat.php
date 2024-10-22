<?php namespace App\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

trait Chat
{
    public function checkConnection()
    {
        try {
            $client = new Client(['http_errors' => false, 'verify' => false]);
            $response = $client->request('GET', env('ROCKET_HOST') . '/api/v1/info', [
                'timeout' => 5,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Auth-Token' => env('ROCKET_TOKEN'),
                    'X-User-Id' => env('ROCKET_ID'),
                ],
            ]);
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                return true;
            } else {
                return false;
            }
        } catch (ConnectException $e) {
            return false;
        } catch (RequestException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function checkConnection2()
    {
        $client = new Client(['http_errors' => false, 'verify' => false]);
        $response = $client->request('GET', env('ROCKET_HOST') . '/api/v1/voip/managementServer/checkConnection?host=https://crmchat.bazarol.ir&port=3001&username=mhdehghanzadeh&password=mhd1377313', [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Auth-Token' => env('ROCKET_TOKEN'),
                'X-User-Id' => env('ROCKET_ID'),
            ],
        ]);
        $body = $response->getBody();
        dd(json_decode($body));
        return json_decode($body);
    }

    public function userInfo($username)
    {
        $client = new Client(['http_errors' => false, 'verify' => false]);
        $response = $client->request('GET', env('ROCKET_HOST') . '/api/v1/users.info', [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Auth-Token' => env('ROCKET_TOKEN'),
                'X-User-Id' => env('ROCKET_ID'),
            ],
            'query' => [
                'username' => $username,
            ],
        ]);
        $body = $response->getBody();
        return json_decode($body);
    }

    public function userLogin($data)
    {
        $client = new Client(['http_errors' => false, 'verify' => false]);
        $response = $client->request('POST', env('ROCKET_HOST') . '/api/v1/login', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
        ]);
        $body = $response->getBody();
        return json_decode($body);
    }

    public function userCreate($data)
    {
        $client = new Client(['http_errors' => false, 'verify' => false]);
        $response = $client->request('POST', env('ROCKET_HOST') . '/api/v1/users.create', [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Auth-Token' => env('ROCKET_TOKEN'),
                'X-User-Id' => env('ROCKET_ID'),
            ],
            'json' => $data,
        ]);
        $body = $response->getBody();
        return json_decode($body);
    }

    public function createChat($data)
    {
        $client = new Client(['http_errors' => false, 'verify' => false]);
        $response = $client->request('POST', env('ROCKET_HOST') . '/api/v1/im.create', [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Auth-Token' => $data['authToken'],
                'X-User-Id' => $data['userId'],
            ],
            'json' => [
                'username' => 'drkhoshniat'
            ],
        ]);
        $body = $response->getBody();
        return json_decode($body);
    }

    public function send_message($data)
    {
        $client = new Client(['http_errors' => false, 'verify' => false]);
        $response = $client->request('POST', env('ROCKET_HOST') . '/api/v1/chat.postMessage', [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Auth-Token' => env('ROCKET_CONSULTANT_TOKEN'),
                'X-User-Id' => env('ROCKET_CONSULTANT_ID'),
            ],
            'json' => $data,
        ]);
        $body = $response->getBody();
        return json_decode($body);
    }

    public function unreadMessages($data)
    {
        try {
            $client = new Client(['http_errors' => false, 'verify' => false]);
            $response = $client->request('GET', env('ROCKET_HOST') . '/api/v1/subscriptions.getOne?roomId=' .  $data['roomId'], [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Auth-Token' => $data['authToken'],
                    'X-User-Id' => $data['userId'],
                ],
                
            ]);
            $body = $response->getBody();
            $unread = json_decode($body);
            return $unread->subscription->unread;
        } catch (\Exception $exception) {
            return null;
        }
       
    }

    

}
