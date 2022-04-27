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

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as TwigEnvironment;

class LoadJobsLogic
{
    private TwigEnvironment $twig;

    private IOLogic $_ioLogic;

    public function __construct(TwigEnvironment $twig, LoggerInterface $logger, string $path)
    {
        $this->twig = $twig;

        $this->_ioLogic = new IOLogic($logger, $path);
    }

    public function loadJobs() : Response
    {
        $jobs = $this->_ioLogic->loadJsonJobsFromFile();

        $timestamp = $this->_ioLogic->getTimestampFromJsonJobsFile();
        $date = date('d.m.Y H:i:s', $timestamp);

        return new Response($this->twig->render(
            '@BrockhausAgContaoRecruitee/LoadJobs/loadJobs.html.twig',
            [
                "jobs" => $jobs,
                "updatedAt" => $date
            ]
        ));
    }
}