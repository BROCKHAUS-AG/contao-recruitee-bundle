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

use BrockhausAg\ContaoRecruiteeBundle\Logic\JobsLogic;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as TwigEnvironment;

/**
 * Class JobsController
 *
 * @Route("/recruitee/jobs",
 *     name="brockhaus_ag_contao_recruitee_jobs",
 *     defaults={
 *         "_scope" = "frontend",
 *         "_token_check" = true
 *     }
 * )
 */

class JobsController extends AbstractController
{
    private $_jobsLogic;

    public function __construct(TwigEnvironment $twig)
    {
        $this->_jobsLogic = new JobsLogic($twig);
    }

    public function __invoke() : Response
    {
        return $this->_jobsLogic->loadJobs();
    }
}
