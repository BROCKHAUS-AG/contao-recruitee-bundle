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

namespace BrockhausAg\ContaoRecruiteeBundle\Controller;

use BrockhausAg\ContaoRecruiteeBundle\Logic\LoadJsonJobsLogic;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class LoadJsonJobsController
 *
 * @Route("/recruitee/load-json-jobs",
 *     name="brockhaus_ag_contao_recruitee_load-json-jobs",
 *     defaults={
 *         "_scope" = "frontend",
 *         "_token_check" = true
 *     }
 * )
 */

class LoadJsonJobsController extends AbstractController
{
    private LoadJsonJobsLogic $_loadJsonJobsLogic;

    public function __construct(LoggerInterface $logger, string $path)
    {
        $this->_loadJsonJobsLogic = new LoadJsonJobsLogic($logger, $path);
    }

    public function __invoke() : Response
    {
        return $this->_loadJsonJobsLogic->loadJobs();
    }
}
