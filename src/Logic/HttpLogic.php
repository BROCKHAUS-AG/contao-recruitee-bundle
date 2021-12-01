<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoRecruiteeBundle\Logic;


class HttpLogic
{
    public function __construct() {}

    public function httpGetWithBearerToken(string $url, string $bearerToken) : ?array
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Authorization: Bearer ". $bearerToken,
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Host: api.recruitee.com",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            echo "Http curl error for url: ". $url. " Error:" . $err;
            return null;
        }
        return json_decode($response, true);
    }
}