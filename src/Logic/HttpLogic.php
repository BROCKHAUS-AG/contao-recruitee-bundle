<?php

declare(strict_types=1);

/*
 * This file is part of Contao Recruitee Bundle.
 *
 * (c) BROCKHAUS AG 2021 <info@brockhaus-ag.de>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/brockhaus-ag/contao-recruitee-bundle
 */

namespace BrockhausAg\ContaoRecruiteeBundle\Logic;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

DEFINE("RECRUITEE_URL", "https://api.recruitee.com/c/");
DEFINE("RECURITEE_URL_COMPANY_ID", "%s");

class HttpLogic
{
    public function __construct() {}

    private function createRecruiteeUrlWithCompanyId(string $companyId) : string
    {
        return RECRUITEE_URL. sprintf(RECURITEE_URL_COMPANY_ID, $companyId);
    }

    public function httpGetWithBearerToken(string $url, string $bearerToken, LoggerInterface $logger) : ?array
    {
        $tries = 0;
        while ($tries < 5) {
            $logger->log(LogLevel::INFO, "Retrieving data from $url");
            $response = $this->directRequest($url, $bearerToken, $logger);
            if($response == null) {
                $tries++;
                $logger->log(LogLevel::INFO, "Retrieving data from $url");
                sleep(10);
                continue;
            }
            return $response;
        }
        $logger->log(LogLevel::WARNING, "Retrieving data from $url was not possible");
        return null;
    }

    private function directRequest(string $url, string $bearerToken, LoggerInterface $logger) : ?array {
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
            $logger->log(LogLevel::ERROR, "HTTP curl error for url: '" . $url . "' with error: " . $err);
            return null;
        }
        return json_decode($response, true);
    }

    public function createCandidatesRequest($candidatePostData, $token, $companyId)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->createRecruiteeUrlWithCompanyId($companyId) ."/candidates",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>json_encode($candidatePostData),
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $token,
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    public function uploadFileForCandidate($tmpName, $name, $candidateId, $token, $companyId){
        $curl = curl_init();

        $curlFile = new \CurlFile($tmpName, "application/pdf",  $name);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->createRecruiteeUrlWithCompanyId($companyId) ."/attachments",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => array('attachment[file]'=> $curlFile,'attachment[candidate_id]' => $candidateId),
            CURLOPT_SAFE_UPLOAD => true,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $token,
                "Content-Type: multipart/form-data"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        }

        return $response;
    }

    public function setAttachmentAsCV($candidateId, $attachmentId, $token, $companyId){
        $url =$this->createRecruiteeUrlWithCompanyId($companyId) ."/candidates/". $candidateId ."/set_as_cv/" .
            $attachmentId;

        $curl = curl_init();
        $header = array(
            "Authorization: Bearer " . $token,
            "Content-Type: application/json"
        );
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        curl_exec($curl);
        curl_close($curl);
    }

    public function setGDPR($candidateId, $companyId, $token){
        $curl = curl_init();
        $defaultTimeZone='UTC';
        date_default_timezone_set($defaultTimeZone);
        $expire_date = date("Y-m-d\Th:i:00.000000\Z", strtotime("+6 months"));

        $postData = array();
        $postData["gdpr_custom_expires_at"] = $expire_date;

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->createRecruiteeUrlWithCompanyId($companyId). "/gdpr/candidates/". $candidateId.
                "/set_gdpr_custom_expires_at",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>json_encode($postData),
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $token,
                "Content-Type: application/json"
            ),
        ));

        curl_exec($curl);
        curl_close($curl);
    }
}