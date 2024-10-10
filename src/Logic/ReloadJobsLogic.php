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

use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as TwigEnvironment;
use Psr\Log\LoggerInterface;


DEFINE("RECRUITEE_URL", "https://api.recruitee.com");
DEFINE("OFFERS_URL", RECRUITEE_URL . "/c/%s/offers");
DEFINE("OFFERS_BY_ID_URL", OFFERS_URL . "/%d");
DEFINE("LOCATIONS_URL", RECRUITEE_URL . "/c/%s/locations");
DEFINE("LOCATIONS_BY_ID_URL", LOCATIONS_URL . "/%d");

class ReloadJobsLogic
{
    private TwigEnvironment $twig;
    private LoggerInterface $logger;

    private IOLogic $_ioLogic;
    private HttpLogic $_httpLogic;

    private array $locations;

    public function __construct(TwigEnvironment $twig, LoggerInterface $logger, string $path)
    {
        $this->twig = $twig;
        $this->logger = $logger;

        $this->_ioLogic = new IOLogic($logger, $path);
        $this->_httpLogic = new HttpLogic();

        $this->locations = $this->_ioLogic->loadRecruiteeConfigLocations();
    }

    private function createJob(array $job, array $offer, string $category): ?array
    {
        $jobDescription = $offer["description"];
        $requirements = $offer["requirements"];

        if ($job["status"] == "published") {
            return array(
                'stellenname' => $job['title'],
                'id' => $job['id'],
                'einsatzort' => $job['location'] ?? '',
                'tags' => $job['offer_tags'],
                '_url' => $job['url'],
                'kategorie' => $category,
                'bildurl' => $job["department"],
                'stellenbeschreibung' => $jobDescription . "\n\n\n" . $requirements,
                'location_ids' => $job['location_ids'] ?? [],
                'office' => $job['office'] ?? false,
                'hybrid' => $job['hybrid'] ?? false,
                'remote' => $job['remote'] ?? false,
            );
        }
        return null;
    }

    private function getJob(string $companyIdentifier, string $bearerToken, string $category): array
    {
        $this->logger->log(LogLevel::INFO, "Getting jobs from API");
        $jobs = $this->getJobsFromApi($companyIdentifier, $bearerToken);
        if($jobs == null) {
            $this->logger->log(LogLevel::CRITICAL, "Jobs from API not found");
            exit;
        }
        $this->logger->log(LogLevel::INFO, "Loaded Jobs");
        $jobDescriptions = array();
        if ($jobs) {
            foreach ($jobs["offers"] as $job) {
                $this->logger->log(LogLevel::INFO, "Getting offer from API");
                $offer = $this->getOfferFromApiById($job["id"], $bearerToken, $companyIdentifier)["offer"];
                if($offer == null || $offer['location_ids'] == null) {
                    $this->logger->log(LogLevel::WARNING, "Offer or location_ids null, skipping offer: " . json_encode($offer));
                    continue;
                }
                $this->logger->log(LogLevel::INFO, "Loaded offer");
                $this->logger->log(LogLevel::INFO, "Getting location from API");
                $job['einsatzort'] = $this->getLocationByIdFromApi($companyIdentifier, $bearerToken, $offer['location_ids'][0]);
                if($job['einsatzort'] == null) {
                    $this->logger->log(LogLevel::WARNING, "Location not found");
                    continue;
                }
                $this->logger->log(LogLevel::INFO, "Loaded location");
                $this->logger->log(LogLevel::INFO, "Creating job");
                $jobDescription = $this->createJob($job, $offer, $category);
                $this->logger->log(LogLevel::INFO, "Job created");
                if ($jobDescription) {
                    array_push($jobDescriptions, $jobDescription);
                }
            }
        }
        return $jobDescriptions;
    }

    private function getJobs(): array
    {
        $jobs = array();
        foreach ($this->locations as $location) {
            $job = $this->getJob($location["companyIdentifier"], $location["bearerToken"], $location["category"]);
            array_push($jobs, $job);
        }
        return $jobs;
    }

    private function createOffersUrlWithCompanyId(string $companyId): string
    {
        return sprintf(OFFERS_URL, $companyId);
    }

    private function createLocationsUrlWithCompanyIdAndLocationId(string $companyId, int $locationId):string
    {
        return sprintf(LOCATIONS_URL, $companyId, $locationId);
    }



    private function createOffersURLWithCompanyIdAndOfferId(string $companyId, int $offerId): string
    {
        return sprintf(OFFERS_BY_ID_URL, $companyId, $offerId);
    }

    private function getJobsFromApi(string $companyId, string $bearerToken): ?array
    {
        $offersUrl = $this->createOffersUrlWithCompanyId($companyId);
        return $this->_httpLogic->httpGetWithBearerToken($offersUrl, $bearerToken, $this->logger);
    }

    private function getLocationByIdFromApi(string $companyId, string $bearerToken, int $locationId): ?array
    {
        $locationsUrlWithId = $this->createLocationsUrlWithCompanyIdAndLocationId($companyId, $locationId);
        return $this->_httpLogic->httpGetWithBearerToken($locationsUrlWithId, $bearerToken, $this->logger);
    }

    private function getOfferFromApiById(int $offerId, string $bearerToken, string $companyId): ?array
    {
        $offersUrl = $this->createOffersURLWithCompanyIdAndOfferId($companyId, $offerId);
        return $this->_httpLogic->httpGetWithBearerToken($offersUrl, $bearerToken, $this->logger);
    }

    private function saveJobs(array $jobs)
    {
        $this->_ioLogic->saveJobsToFile($jobs);
    }

    public function loadJobs(): Response
    {
        $jobs = $this->getJobs();
        $this->saveJobs($jobs);

        return new Response($this->twig->render(
            '@BrockhausAgContaoRecruitee/ReloadJobs/reloadJobs.html.twig', [
                "jobs" => $jobs
            ]
        ));
    }
}