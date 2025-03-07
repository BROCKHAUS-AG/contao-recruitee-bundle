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

use Contao\CoreBundle\Monolog\ContaoContext;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

DEFINE("PATH", "%s/settings/brockhaus-ag/contao-recruitee-bundle");
DEFINE("JOBS_FILE", "%s/recruiteeJobs.json");

class IOLogic {

    private LoggerInterface $logger;
    private string $path;

    public function __construct(LoggerInterface $logger, string $path)
    {
        $this->logger = $logger;
        $this->path = $path;
    }

    private function checkIfFileExists(string $file)
    {
        if (!file_exists($file)) {
            $errorMessage = "File: \"". $file. "\" could not be found. Please create it!";
            $this->logger->log(
                LogLevel::WARNING, $errorMessage,
                ['contao' => new ContaoContext(__METHOD__, 'TL_ACCESS')]
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
        $path = $this->createPath(). "/config.json";
        return $this->loadJsonFileAndDecode($path);
    }

    public function loadAlternateApplicationURL()
    {
        return $this->loadRecruiteeConfig()["alternateApplicationURL"];
    }

    public function loadRecruiteeConfigLocations() : array
    {
        return $this->loadRecruiteeConfig()["locations"];
    }

    public function loadRecruiteeConfigWebsites() : array
    {
        return $this->loadRecruiteeConfig()["websites"];
    }

    public function createPathWithJobsFile() : string
    {
        $path = $this->createPath();
        return sprintf(JOBS_FILE, $path);
    }

    private function createPath(): string
    {
        return sprintf(PATH, $this->path);
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

    public function getPrivateTokenByServerName(string $givenServerName) : string
    {
        $privateToken = "";
        foreach($this->loadRecruiteeConfigWebsites() as $website) {
            if($website["serverName"] == $givenServerName) {
                $privateToken = $website["reCaptchaPrivateToken"];
            }
        }
        return $privateToken;
    }
}