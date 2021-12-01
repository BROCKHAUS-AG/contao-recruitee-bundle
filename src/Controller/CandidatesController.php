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

use BrockhausAg\ContaoRecruiteeBundle\Logic\CandidatesLogic;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as TwigEnvironment;

/**
 * Class CandidatesController
 *
 * @Route("/recruitee/candidates",
 *     name="brockhaus_ag_contao_recruitee_candidates",
 *     defaults={
 *         "_scope" = "frontend",
 *         "_token_check" = true
 *     }
 * )
 */

class CandidatesController extends AbstractController
{
    private $_candidatesLogic;

    public function __construct(TwigEnvironment $twig)
    {
        $this->_candidatesLogic = new CandidatesLogic($twig);
    }

    /*public function __invoke($_POST, $_FILES)
    {
        $this->_candidatesLogic->addCandidate($_POST, $_FILES);
    }*/
}
