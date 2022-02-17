<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 11/28/18
 * Time: 12:57 PM
 */

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\HandlerStack;

class Requester
{
    private $token = 'e929be7849b90e553082e592552739082c7614ab';
    private $api_url = 'https://blueflamecornwallltd.simprosuite.com/api/v1.0/';
    /** @var Client */
    private $client;
    private $curl;

    public function __construct()
    {
        $this->curl = new CurlMultiHandler();
        $handler = HandlerStack::create($this->curl);
        $this->client = new Client(['handler' => $handler]);
    }

    public function tick()
    {
        $this->curl->tick();

    }

    public function setApiUrl($url)
    {
        $this->api_url = $url;
    }

    /**
     * @param $url
     * @param $headers
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getRequestAsync($url, $headers)
    {
        $params = '&';
        foreach ($headers as $key => $value) {
            $params .= $key . '=' . $value . '&';
        }


        return $this->client->requestAsync('GET', $this->api_url . $url . ((strpos($url, '?') != false) ? '&' : '?') . 'access_token=' . $this->token . $params, [
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);
    }
}