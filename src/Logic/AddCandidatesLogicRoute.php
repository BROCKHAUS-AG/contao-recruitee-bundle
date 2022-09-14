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

use BrockhausAg\ContaoRecruiteeBundle\Models\Candidate;
use BrockhausAg\ContaoRecruiteeBundle\Models\CandidatePost;
use BrockhausAg\ContaoRecruiteeBundle\Models\Field;
use Contao\CoreBundle\Monolog\ContaoContext;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class AddCandidatesLogicRoute
{
    private LoggerInterface $logger;

    private HttpLogic $_httpLogic;

    private array $locations;

    public function __construct(LoggerInterface $logger, string $path)
    {
        $this->logger = $logger;
        $this->_httpLogic = new HttpLogic();


        $ioLogic = new IOLogic($logger, $path);
        $this->locations = $ioLogic->loadRecruiteeConfigLocations();
    }

    public function addCandidate(array $submittedData, array $formData, ?array $files) : void
    {
        $this->sendToRecruitee($submittedData, $formData, $files);
        $this->createResponse();
    }

    private function sendToRecruitee(array $submittedData, array $formData, ?array $files) : void
    {
        $location = $this->getLocationByAlias($formData["alias"]);

        $offerId = $submittedData["jobID"];

        $salutation = $submittedData["bw_anrede"];
        $title = $submittedData["bw_titel"];
        $firstName = $submittedData["bw_vorname"];
        $lastName = $submittedData["bw_name"];
        $email = $submittedData["bw_email"];
        $message = null;
        if ($submittedData["profil_sonstiges"]) {
            $message = $submittedData["profil_sonstiges"];
        }
        $github = $submittedData["github"];
        $linkedin = $submittedData["linkedin"];
        $xing = $submittedData["xing"];


        $coverLetter = $files["anschreiben"] ? array(
            "tmp_name" => $files["anschreiben"]->getRealPath(),
            "name" => $files["anschreiben"]->getClientOriginalName()
        ) : [];

        $curriculumVitae = $files["lebenslauf"] ? array(
            "tmp_name" => $files["lebenslauf"]->getRealPath(),
            "name" => $files["lebenslauf"]->getClientOriginalName()
        ) : [];

        $certificate = $files["zeugnisse"] ? array(
            "tmp_name" => $files["zeugnisse"]->getRealPath(),
            "name" => $files["zeugnisse"]->getClientOriginalName()
        ) : [];

        $picture = $files["foto"] ? array(
            "tmp_name" => $files["foto"]->getRealPath(),
            "name" => $files["foto"]->getClientOriginalName()
        ) : [];

        $additionalSource = $submittedData["bw_quelle"];

        $this->createNewCandidate($location, $offerId, $salutation, $title, $firstName, $lastName, $email, $message,
            $github, $linkedin, $xing, $additionalSource, $coverLetter, $curriculumVitae, $certificate, $picture);
    }

    private function getLocationByAlias(string $alias) : array
    {
        foreach ($this->locations as $location) {
            if (strcmp("bewerbung-". $location["category"], $alias) == 0) {
                return $location;
            }
        }
        $this->logger->log(
            LogLevel::WARNING, "Location with name ". $alias. " could not be found",
            ['contao' => new ContaoContext(__METHOD__, 'TL_ACCESS')]
        );
        echo "Failed -> you can find error log in the Contao Backend";
        exit();
    }

    private function createNewCandidate(array $location, $offerId, string $salutation, ?string $title,
                                        string $firstName, string $lastName, string $email, ?string $message,
                                        ?string $github, ?string $linkedin, ?string $xing, ?string $additionalSource,
                                        array $coverLetter = [], array $curriculumVitae = [], array $certificate = [],
                                        array $picture = []) : void
    {
        $fields = $this->createFields($salutation, $title, $firstName, $lastName, $github, $linkedin, $xing);

        $token = $location["bearerToken"];
        $companyId = $location["companyIdentifier"];
        $category = $location["category"];

        $candidate = new Candidate(
            $firstName.' '.$lastName,
            array($category."-Website", $additionalSource),
            $fields,
            array(0 => $email),
            array(),
            $message
        );

        $candidatePost = new CandidatePost($candidate, array(0 => $offerId));
        $candidateResponse= $this->_httpLogic->createCandidatesRequest($candidatePost, $token, $companyId);

        $candidateResult = json_decode($candidateResponse, true);
        $candidateId = $candidateResult["candidate"]["id"];

        $this->sendAttachments($candidateId, $token, $companyId, $coverLetter, $curriculumVitae, $certificate,
            $picture);

        $this->_httpLogic->setGDPR($candidateId, $companyId, $token);
    }

    private function createFields(string $salutation, ?string $title, string $firstName, string $lastName,
                                  ?string $github, ?string $linkedin, ?string $xing) : array
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

    private function sendAttachments(int $candidateId, string $token, string $companyId, array $coverLetter = [],
                                     array $curriculumVitae = [], array $certificate = [], array $picture = []) : void
    {
        if($coverLetter)
        {
            $this->_httpLogic->uploadFileForCandidate($coverLetter["tmp_name"], $coverLetter["name"],
                $candidateId, $token, $companyId);
        }
        if($curriculumVitae){
            $this->uploadFileAndAttachCv($candidateId, $curriculumVitae , $token, $companyId);
        }
        if($certificate)
        {
            $this->_httpLogic->uploadFileForCandidate($certificate["tmp_name"], $certificate["name"],
                $candidateId, $token, $companyId);
        }
        if($picture)
        {
            $this->_httpLogic->uploadFileForCandidate($picture["tmp_name"], $picture["name"],
                $candidateId, $token, $companyId);
        }
    }

    private function uploadFileAndAttachCv($candidateId, $curriculumVitae , string $token, string $companyId) : void
    {
        $responseJson = $this->_httpLogic->uploadFileForCandidate($curriculumVitae["tmp_name"],
            $curriculumVitae["name"], $candidateId, $token, $companyId);

        $response = json_decode($responseJson, true);
        $cvId = $response["attachment"]["id"];
        $this->_httpLogic->setAttachmentAsCV($candidateId, $cvId, $token, $companyId,);
    }

    private function createResponse() : void
    {
        $response_code = 200;
        $response_message = '<h1 style="text-align: center;" class="ok">Ihre Bewerbung wurde versendet.</h1>';
        $_SESSION['recruitee_response_code'] = $response_code;
        $_SESSION['recruitee_response_message'] = $response_message;
    }
}