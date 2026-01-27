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

        // FIX 1: Remote / Hybrid Logik für Tellent/Recruitee
        // Prüft auf 'remote_type' (z.B. "remote_full", "hybrid")
        $remoteType = $offer['remote_type'] ?? $job['remote_type'] ?? null;
        $isRemote = ($remoteType === 'remote_full');
        $isHybrid = ($remoteType === 'hybrid');
        // Wenn kein Typ da ist oder explizit "office_only", dann ist es Office
        $isOffice = ($remoteType === null || $remoteType === 'office_only');

        // FIX 2: Tags (Der korrekte API-Name ist 'tags')
        $tags = $job['tags'] ?? [];

        // FIX 3: URL (Der korrekte API-Name für den öffentlichen Link ist 'careers_url')
        $url = $job['careers_url'] ?? $job['url'] ?? '';

        // FIX 4: Einsatzort Fallback
        // Wir nehmen den Ort, den wir im Loop (siehe unten) ermittelt haben.
        // Falls der leer ist, nehmen wir als Notlösung das Textfeld 'location'.
        $finalLocation = "";
        if (!empty($job['einsatzort'])) {
            $finalLocation = $job['einsatzort'];
        } else {
            $finalLocation = $job['location'] ?? '';
        }

        // FIX 5: Bild URL (Social Media Image statt Abteilung)
        $bildUrl = "";
        // Prüfen ob im Detail-Datensatz ($offer) ein Bild liegt
        if (isset($offer['social_media_image']) && !empty($offer['social_media_image'])) {
            $bildUrl = $offer['social_media_image'];
        }
        // Manchmal liegt es auch direkt im Listen-Datensatz ($job)
        elseif (isset($job['social_media_image']) && !empty($job['social_media_image'])) {
            $bildUrl = $job['social_media_image'];
        }

        if ($job["status"] == "published") {
            return array(
                'stellenname' => $job['title'],
                'id' => $job['id'],
                'einsatzort' => $finalLocation,
                'tags' => $tags,
                '_url' => $url,
                'kategorie' => $category,
                'bildurl' => $bildUrl,
                'stellenbeschreibung' => $jobDescription . "\n\n\n" . $requirements,
                'location_ids' => $job['location_ids'] ?? [],
                'office' => $isOffice,
                'hybrid' => $isHybrid,
                'remote' => $isRemote,
            );
        }
        return null;
    }

    private function getJob(string $companyIdentifier, string $bearerToken, string $category): array
    {
        $this->logger->log(LogLevel::INFO, "Getting jobs from API for " . $companyIdentifier);
        $jobs = $this->getJobsFromApi($companyIdentifier, $bearerToken);

        if($jobs == null) {
            $this->logger->log(LogLevel::CRITICAL, "Jobs from API not found. Check Bearer Token!");
            exit;
        }

        $this->logger->log(LogLevel::INFO, "Loaded Jobs count: " . count($jobs['offers'] ?? []));
        $jobDescriptions = array();

        if ($jobs && isset($jobs["offers"])) {
            foreach ($jobs["offers"] as $job) {

                // Detailabfrage für jeden Job
                $offerResponse = $this->getOfferFromApiById($job["id"], $bearerToken, $companyIdentifier);
                $offer = $offerResponse["offer"] ?? null;

                if($offer == null) {
                    continue;
                }

                // FIX: Verbesserte Location Logik
                $cityName = "";

                // Versuch 1: Über Location ID (wenn vorhanden) die Stadt abfragen
                if (isset($offer['location_ids']) && count($offer['location_ids']) > 0) {
                    $locationResponse = $this->getLocationByIdFromApi($companyIdentifier, $bearerToken, $offer['location_ids'][0]);
                    if (isset($locationResponse['location']) && isset($locationResponse['location']['city'])) {
                        $cityName = $locationResponse['location']['city'];
                    }
                }

                // Versuch 2: Fallback auf das Textfeld 'location' im Job selbst
                // Das greift, wenn keine ID hinterlegt ist (z.B. bei Remote Jobs)
                if (empty($cityName) && isset($job['location']) && is_string($job['location'])) {
                    $cityName = $job['location'];
                }

                // Wir speichern das Ergebnis im Array, damit createJob es nutzen kann
                $job['einsatzort'] = $cityName;

                $jobDescription = $this->createJob($job, $offer, $category);

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
            $result = $this->getJob($location["companyIdentifier"], $location["bearerToken"], $location["category"]);
            // FIX: Array merge nutzen, da getJob ein Array zurückgibt
            if (is_array($result)) {
                $jobs = array_merge($jobs, $result);
            }
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