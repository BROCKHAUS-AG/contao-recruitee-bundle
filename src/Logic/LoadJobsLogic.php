<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoRecruiteeBundle\Logic;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as TwigEnvironment;
use Psr\Log\LoggerInterface;


DEFINE("RECRUITEE_URL", "https://api.recruitee.com");
DEFINE("OFFERS_URL", RECRUITEE_URL."/c/%s/offers");
DEFINE("OFFERS_BY_ID_URL", OFFERS_URL. "/%d");

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