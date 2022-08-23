<?php

use BrockhausAg\ContaoRecruiteeBundle\Logic\AddCandidatesLogic;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AddCandidateController
 *
 * @Route("/recruitee/example",
 *     name="brockhaus_ag_contao_recruitee_AddCandidateController",
 *     defaults={
 *         "_scope" = "frontend",
 *         "_token_check" = true
 *     }
 * )
 */
class AddCandidateController
{
    private AddCandidatesLogic $_addCandidatesLogic;

    public function __construct(LoggerInterface $logger, string $path)
    {
        $this->_addCandidatesLogic = new AddCandidatesLogic($logger, $path);
    }

    public function createFileIfNotExists(string $path)
    {
        if (!file_exists($path)) {
            touch($path);
        }
    }


    public function __invoke() : Response
    {
        $this->createFileIfNotExists("/var/www/html/contao/testing/test.xml");
        $this->createFileIfNotExists("/var/www/html/contao/testing/test2.xml");
        $this->createFileIfNotExists("/var/www/html/contao/testing/test3.xml");
        file_put_contents("/var/www/html/contao/testing/test.xml", "hi erster");
        file_put_contents("/var/www/html/contao/testing/test2.xml", "hi zweiter");
        file_put_contents("/var/www/html/contao/testing/test3.xml", "hi dritter");
        return new Response("Hello world");
    }

    /**
     * @Hook("processFormData")
     */
    public function onAddCandidate(array $submittedData, array $formData, ?array $files): void
    {
        /*  if ($formData['formID'] == 'bewerbung')
        {
        $this->_addCandidatesLogic->addCandidate($submittedData, $formData, $files);
        }*/
    }
}