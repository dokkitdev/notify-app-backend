<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 11/26/18
 * Time: 11:17 AM
 */

namespace App\Service;


class CurlService
{
    private $token = 'e929be7849b90e553082e592552739082c7614ab';
    const API_URL = 'https://blueflamecornwallltd.simprosuite.com';

    public function getMethod($url, $params = null)
    {
//        dump(self::API_URL . $url . '?access_token=' . $this->token);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
        curl_setopt($ch, CURLOPT_URL, self::API_URL . $url . '?access_token=' . $this->token);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $result = curl_exec($ch);

        curl_close($ch);
        return json_decode($result);
    }
}