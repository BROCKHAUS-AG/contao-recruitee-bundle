<?php

declare(strict_types=1);

/*
 * This file is part of Contao Microsoft SSO Bundle.
 *
 * (c) BROCKHAUS AG 2021 <info@brockhaus-ag.de>
 * Author Niklas Lurse (INoTime) <nlurse@brockhaus-ag.de>
 *
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/BROCKHAUS-AG/contao-microsoft-sso-bundle
 */

namespace BrockhausAg\ContaoRecruiteeBundle\Logic;


use BrockhausAg\ContaoRecruiteeBundle\Models\Candidate;
use BrockhausAg\ContaoRecruiteeBundle\Models\CandidatePost;
use BrockhausAg\ContaoRecruiteeBundle\Models\Field;
use Contao\CoreBundle\Monolog\ContaoContext;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class AddCandidatesLogic
{
    private LoggerInterface $logger;

    private array $locations;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;

        $ioLogic = new IOLogic($logger);
        $this->locations = $ioLogic->loadRecruiteeConfigLocations();
    }

    public function addCandidate(array $formData, array $submittedData, ?array $files) : void
    {
        $this->sendToRecruitee($formData, $submittedData, $files);
        $this->logger->log(
            LogLevel::INFO, "add candidates was called",
            ['contao' => new ContaoContext(__METHOD__, 'TL_ACCESS')]
        );
    }

    private function sendToRecruitee(array $formData, array $submittedData, ?array $files) : void
    {
        $page = $this->getPageNameByAlias($submittedData["alias"]);

        $offerId = $formData["jobID"];

        $salutation = $formData["bw_anrede"];
        $title = $formData["bw_titel"];
        $firstName = $formData["bw_vorname"];
        $lastName = $formData["bw_name"];
        $email = $formData["bw_email"];
        $message = $formData["profil_sonstiges"];
        $github = $formData["github"];
        $linkedin = $formData["linkedin"];
        $xing = $formData["xing"];

        $coverLetter = $files["anschreiben"];
        $curriculumVitae = $files["lebenslauf"];
        $certificate = $files["zeugnisse"];
        $picture = $files["foto"];

        $additionalSource = $formData["bw_quelle"];


        $this->createNewCandidate($page, $offerId, $salutation, $title, $firstName, $lastName, $email, $message,
            $github, $linkedin, $xing, $coverLetter, $curriculumVitae, $certificate, $picture, $additionalSource);

        $response_code = 200;
        $response_message = '<h1 style="text-align: center;" class="ok">Ihre Bewerbung wurde versendet.</h1>';


        $_SESSION['coveto_response_code'] = $response_code;
        $_SESSION['coveto_response_message'] = $response_message;
    }

    private function getPageNameByAlias(string $alias) : string
    {
        foreach ($this->locations as $location) {
            if (strcmp("bewerbung-". $location["name"], $alias) == 0) {
                return $location["name"];
            }
        }
        $this->logger->log(
            LogLevel::WARNING, "Location with name ". $location["name"]. " could not be found",
            ['contao' => new ContaoContext(__METHOD__, 'TL_ACCESS')]
        );
        echo "Failed -> you can find error log in the Contao Backend";
        exit();
    }

    private function createNewCandidate(string $page, $offerId, string $salutation, string $title, string $firstName,
                                        string $lastName, string $email, string $message, string $github,
                                        string $linkedin, string $xing, $coverLetter, $curriculumVitae, $certificate,
                                        $picture, $additionalSource) : void
    {
        $fields = $this->createFields($salutation, $title, $firstName, $lastName, $github, $linkedin, $xing);

        $token = "";
        $companyId = "";
        foreach ($this->locations as $location) {
            if (strcmp($location["name"], $page) == 0) {
                $token = $location["bearerToken"];
                $companyId = $location["companyIdentifier"];
                break;
            }
        }

        $candidate = new Candidate(
            $firstName.' '.$lastName,
            array(0 => $email),
            array(),
            $message,
            array($page."-Website", $additionalSource),
            $fields);

        $candidatePost = new CandidatePost($candidate, array(0 => $offerId));
        $result = $this->createApplicantRequest($candidatePost, $token, $companyId);

        $json = json_decode($result, true);
        $candidateId = $json["candidate"]["id"];

        if($coverLetter)
        {
            $messageUploaded = $this->uploadFileForCandidate($coverLetter["tmp_name"], $coverLetter["name"],
                $candidateId, $token, $companyId);
        }
        if($curriculumVitae)
        {
            $cvUploaded = $this->uploadFileForCandidate($curriculumVitae["tmp_name"], $curriculumVitae["name"],
                $candidateId, $token, $companyId);
            $cvJson = json_decode($cvUploaded, true);
            $cvId = $cvJson["attachment"]["id"];

            $this->setAttachmentAsCV($candidateId, $cvId, $token, $companyId);
        }
        if($certificate)
        {
            $certificateUploaded = $this->uploadFileForCandidate($certificate["tmp_name"], $certificate["name"],
                $candidateId, $token, $companyId);
        }
        if($picture)
        {
            $profilePicUploaded = $this->uploadFileForCandidate($picture["tmp_name"], $picture["name"],
                $candidateId, $token, $companyId);
        }


        $this->setGDPR($candidateId, $companyId, $token);
    }

    private function createFields(string $salutation, string $title, string $firstName, string $lastName,
                                  string $github, string $linkedin, string $xing) : array
    {
        $fields = array();
        array_push($fields, new Field("Anrede", array($salutation)));
        array_push($fields, new Field("Vorname", array($firstName)));
        array_push($fields, new Field("Nachname", array($lastName)));
        if($title) {
            array_push($fields, new Field("Titel", array($title)));
        }
        if($github) {
            array_push($fields, new Field("Github", array($github)));
        }
        if($linkedin) {
            array_push($fields, new Field("LinkedIn", array($linkedin)));
        }
        if($xing) {
            array_push($fields, new Field("XING", array($xing)));
        }
        return $fields;
    }

    private function createApplicantRequest($candidatePostData, $token, $companyId)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.recruitee.com/c/". $companyId ."/candidates",
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

    private function uploadFileForCandidate($tmpName, $name, $candidateId, $token, $companyId){
        $curl = curl_init();

        $curlfile = new \CurlFile($tmpName, "application/pdf",  $name);

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.recruitee.com/c/" . $companyId ."/attachments",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => array('attachment[file]'=> $curlfile,'attachment[candidate_id]' => $candidateId),
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

    private function setAttachmentAsCV($candidateId, $attachmentId, $token, $companyId){
        $url = "https://api.recruitee.com/c/". $companyId ."/candidates/". $candidateId ."/set_as_cv/" . $attachmentId;

        $curl = curl_init();
        $header = array(
            "Authorization: Bearer " . $token,
            "Content-Type: application/json"
        );
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        $response = curl_exec($curl);
        curl_close($curl);
    }

    private function setGDPR($candidateId, $companyId, $token){
        $curl = curl_init();
        $defaultTimeZone='UTC';
        date_default_timezone_set($defaultTimeZone);
        $expire_date = date("Y-m-d\Th:i:00.000000\Z", strtotime("+6 months"));

        $postData = array();
        $postData["gdpr_custom_expires_at"] = $expire_date;

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.recruitee.com/c/".$companyId."/gdpr/candidates/".$candidateId."/set_gdpr_custom_expires_at",
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

        $response = curl_exec($curl);

        curl_close($curl);
    }


}