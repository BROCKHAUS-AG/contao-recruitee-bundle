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

class IOLogic {

    private $logger;

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

    public function loadRecruiteeConfig() : array
    {
        return $this->loadJsonFileAndDecode(PATH. "config.json");
    }

    public function saveJsonJobsToFile(string $jobs)
    {
        $fileHandle = fopen(PATH. 'recruiteeJobs.json', 'w');
        fwrite($fileHandle, $jobs);
        fclose($fileHandle);
    }
}