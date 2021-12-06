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

use Contao\CoreBundle\Monolog\ContaoContext;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

DEFINE("PATH", "/html/contao/settings/brockhaus-ag/contao-recruitee-bundle/");
DEFINE("JOBS_FILE", "%s/recruiteeJobs.json");

class IOLogic {

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    private function checkIfFileExists(string $file)
    {
        if (!file_exists($file)) {
            $errorMessage = 'File: "'. $file. " could not be found. Please create it!";
            $this->logger->log(
                LogLevel::WARNING, $errorMessage,
                ['contao' => new ContaoContext(__METHOD__, TL_ACCESS)]
            );
            echo $errorMessage;
            exit();
        }
    }

    private function loadJsonFileAndDecode(string $file) : ?array
    {
        $this->checkIfFileExists($file);
        $fileContent = file_get_contents($file);
        return json_decode($fileContent, true);
    }

    private function loadRecruiteeConfig() : array
    {
        return $this->loadJsonFileAndDecode(PATH. "config.json");
    }

    public function loadRecruiteeConfigLocations() : array
    {
        return $this->loadRecruiteeConfig()["locations"];
    }

    private function createPathWithJobsFile() : string
    {
        return sprintf(JOBS_FILE, PATH);
    }

    public function saveJobsToFile(array $jobs)
    {
        $array = array("timestamp" => time(), "jobs" => $jobs);
        $jsonArray = json_encode($array);
        $fileHandle = fopen($this->createPathWithJobsFile(), 'w');
        fwrite($fileHandle, $jsonArray);
        fclose($fileHandle);
    }

    private function loadJobs() : array
    {
        return $this->loadJsonFileAndDecode($this->createPathWithJobsFile());
    }

    public function loadJsonJobsFromFile() : array
    {
        return $this->loadJobs()["jobs"];
    }

    public function getTimestampFromJsonJobsFile() : int
    {
        return $this->loadJobs()["timestamp"];
    }
}