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

namespace BrockhausAg\ContaoRecruiteeBundle\Controller;

use BrockhausAg\ContaoRecruiteeBundle\Logic\ReloadJobsLogic;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as TwigEnvironment;
use Psr\Log\LoggerInterface;

/**
 * Class ReloadJobsController
 *
 * @Route("/recruitee/reload-jobs",
 *     name="brockhaus_ag_contao_recruitee_reload_jobs",
 *     defaults={
 *         "_scope" = "frontend",
 *         "_token_check" = true
 *     }
 * )
 */
class ReloadJobsController extends AbstractController
{
    private $_loadJobsLogic;

    public function __construct(TwigEnvironment $twig,
                                LoggerInterface $logger)
    {
        $this->_loadJobsLogic = new ReloadJobsLogic($twig, $logger);
    }

    public function __invoke() : Response
    {
        return $this->_loadJobsLogic->loadJobs();
    }
}
