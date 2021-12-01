<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoRecruiteeBundle\Logic;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as TwigEnvironment;

class JobsLogic
{
    private $twig;

    public function __construct(TwigEnvironment $twig)
    {
        $this->twig = $twig;
    }

    public function loadJobs() : Response
    {
        return new Response($this->twig->render(
            '@BrockhausAgContaoRecruitee/LoadJobs/jobs.html.twig', []
        ));
    }
}