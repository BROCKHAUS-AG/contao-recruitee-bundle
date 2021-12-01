<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoRecruiteeBundle\Logic;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as TwigEnvironment;
use Psr\Log\LoggerInterface;

class LoadJobsLogic
{
    private $twig;

    private $_ioLogic;
    private $_httpLogic;

    private $locations;

    public function __construct(TwigEnvironment $twig,
                                LoggerInterface $logger)
    {
        $this->twig = $twig;

        $this->_ioLogic = new IOLogic($logger);
        $this->_httpLogic = new HttpLogic();

        $this->locations = $this->_ioLogic->loadRecruiteeConfig()["locations"];
    }

    private function getJobsFromApi(string $companyId, string $bearerToken) : ?array
    {
        $url = "https://api.recruitee.com/c/".$companyId."/offers";
        return $this->_httpLogic->httpGetWithBearerToken($url, $bearerToken);
    }

    private function getOfferFromApiById(int $offerId, string $bearerToken, string $companyId) : ?array
    {
        $url = "https://api.recruitee.com/c/" . $companyId . "/offers/".$offerId;
        return $this->_httpLogic->httpGetWithBearerToken($url, $bearerToken);
    }

    private function createJob($job, array $offer, string $category) : ?array
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

    private function getJob(string $companyIdentifier, string $bearerToken, string $category) : ?string
    {
        $jobs = $this->getJobsFromApi($companyIdentifier, $bearerToken);

        if ($jobs) {
            $jobDescriptions = array();
            foreach($jobs["offers"] as $job) {
                $offer = $this->getOfferFromApiById($job["id"], $bearerToken, $companyIdentifier);
                $jobDescription = $this->createJob($job, $offer["offer"], $category);
                if ($jobDescription) {
                    array_push($jobDescriptions, $jobDescription);
                }
            }
            return json_encode($jobDescriptions);
        }
        return null;
    }

    private function getJobs() : string
    {
        $jobs = array();
        foreach ($this->locations as $location) {
            $job = $this->getJob($location["companyIdentifier"], $location["bearerToken"], $location["category"]);
            array_push( $jobs, $job);
        }

        return json_encode($jobs);
    }

    public function loadJobs() : Response
    {
        $jobs = $this->getJobs();

        return new Response($this->twig->render(
            '@BrockhausAgContaoRecruitee/LoadJobs/loadJobs.html.twig', [
                "jobs" => $jobs
            ]
        ));
    }
}