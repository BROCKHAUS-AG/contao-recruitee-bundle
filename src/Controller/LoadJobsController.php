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

use BrockhausAg\ContaoRecruiteeBundle\Logic\LoadJobsLogic;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment as TwigEnvironment;

/**
 * Class LoadJobsController
 *
 * @Route("/recruitee/load-jobs",
 *     name="brockhaus_ag_contao_recruitee_load-jobs",
 *     defaults={
 *         "_scope" = "frontend",
 *         "_token_check" = true
 *     }
 * )
 */

class LoadJobsController extends AbstractController
{
    private LoadJobsLogic $_loadJobsLogic;

    public function __construct(TwigEnvironment $twig,
                                LoggerInterface $logger)
    {
        $this->_loadJobsLogic = new LoadJobsLogic($twig, $logger);
    }

    public function __invoke() : Response
    {
        return $this->_loadJobsLogic->loadJobs();
    }
}
