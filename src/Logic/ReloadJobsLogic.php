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

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as TwigEnvironment;
use Psr\Log\LoggerInterface;


DEFINE("RECRUITEE_URL", "https://api.recruitee.com");
DEFINE("OFFERS_URL", RECRUITEE_URL."/c/%s/offers");
DEFINE("OFFERS_BY_ID_URL", OFFERS_URL. "/%d");

class ReloadJobsLogic
{
    private TwigEnvironment $twig;

    private IOLogic $_ioLogic;
    private HttpLogic $_httpLogic;

    private $locations;

    public function __construct(TwigEnvironment $twig,
                                LoggerInterface $logger)
    {
        $this->twig = $twig;

        $this->_ioLogic = new IOLogic($logger);
        $this->_httpLogic = new HttpLogic();

        $this->locations = $this->_ioLogic->loadRecruiteeConfig()["locations"];
    }

    private function createJob(array $job, array $offer, string $category) : ?array
    {
        $jobDescription = $offer["description"];
        $requirements = $offer["requirements"];

        if($job["status"] == "internal" || $job["status"] == "published") {
            return array(
                'stellenname' => $job['title'],
                'id' => $job['id'],
                'einsatzort' => $job['city'],
                '_url' => $job['url'],
                'kategorie'=> $category,
                'bildurl' => $job["department"],
                'stellenbeschreibung' => $jobDescription . "\n\n\n".$requirements
            );
        }
        return null;
    }

    private function getJob(string $companyIdentifier, string $bearerToken, string $category) : array
    {
        $jobs = $this->getJobsFromApi($companyIdentifier, $bearerToken);
        $jobDescriptions = array();
        if ($jobs) {
            foreach($jobs["offers"] as $job) {
                $offer = $this->getOfferFromApiById($job["id"], $bearerToken, $companyIdentifier)["offer"];
                $jobDescription = $this->createJob($job, $offer, $category);
                if ($jobDescription) {
                    array_push($jobDescriptions, $jobDescription);
                }
            }
        }
        return $jobDescriptions;
    }

    private function getJobs() : array
    {
        $jobs = array();
        foreach ($this->locations as $location) {
            $job = $this->getJob($location["companyIdentifier"], $location["bearerToken"], $location["category"]);
            array_push( $jobs, $job);
        }
        return $jobs;
    }

    private function createOffersUrlWithCompanyId(string $companyId) : string
    {
        return sprintf(OFFERS_URL, $companyId);
    }

    private function createOffersURLWithCompanyIdAndOfferId(string $companyId, int $offerId) : string
    {
        return sprintf(OFFERS_BY_ID_URL, $companyId, $offerId);
    }

    private function getJobsFromApi(string $companyId, string $bearerToken) : ?array
    {
        $offersUrl = $this->createOffersUrlWithCompanyId($companyId);
        return $this->_httpLogic->httpGetWithBearerToken($offersUrl, $bearerToken);
    }

    private function getOfferFromApiById(int $offerId, string $bearerToken, string $companyId) : ?array
    {
        $offersUrl = $this->createOffersURLWithCompanyIdAndOfferId($companyId, $offerId);
        return $this->_httpLogic->httpGetWithBearerToken($offersUrl, $bearerToken);
    }

    private function saveJobs(array $jobs)
    {
        $jsonJobs = json_encode($jobs);
        $this->_ioLogic->saveJsonJobsToFile($jsonJobs);
    }

    public function loadJobs() : Response
    {
        $jobs = $this->getJobs();
        $this->saveJobs($jobs);

        return new Response($this->twig->render(
            '@BrockhausAgContaoRecruitee/LoadJobs/loadJobs.html.twig', [
                "jobs" => $jobs
            ]
        ));
    }
}