<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;

class HttpService
{
    protected function fetch($url)
    {
        $client   = HttpClient::create();
        $response = $client->request('GET', $url);
        
        return (object)[
            'code' => $response->getStatusCode(),
            'html' => $response->getContent(),
        ];
    }
}
