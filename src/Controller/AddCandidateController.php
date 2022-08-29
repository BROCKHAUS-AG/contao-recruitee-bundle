<?php

namespace BrockhausAg\ContaoRecruiteeBundle\Controller;

use BrockhausAg\ContaoRecruiteeBundle\Logic\AddCandidatesLogic;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use BrockhausAg\ContaoRecruiteeBundle\Logic\LoadJsonJobsLogic;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AddCandidateController
 *
 * @Route("/recruitee/example",
 *     name="brockhaus_ag_contao_recruitee_add_candidate",
 *     defaults={
 *         "_scope" = "frontend",
 *         "_token_check" = true
 *     }
 * )
 */
class AddCandidateController extends AbstractController
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


    public function __invoke(Request $request): Response
    {
        $this->createFileIfNotExists("/var/www/html/contao/testing/test.xml");
        $this->createFileIfNotExists("/var/www/html/contao/testing/test2.xml");
        $this->createFileIfNotExists("/var/www/html/contao/testing/test3.xml");
        $submittedData = array(
            "jobID" => $request->request->get("jobID"),
            "bw_anrede" => $request->request->get("bw_anrede"),
            "bw_vorname" => $request->request->get("bw_vorname"),
            "bw_name" => $request->request->get("bw_name"),
            "strasse" => $request->request->get("strasse"),
            "ort" => $request->request->get("ort"),
            "bw_email" => $request->request->get("bw_email"),
            "profil_sonstiges" => $request->request->get("profil_sonstiges"),
            "github" => $request->request->get("github"),
            "linkedin" => $request->request->get("linkedin"),
            "xing" => $request->request->get("xing"),
            "bw_quelle" => $request->request->get("bw_quelle"),
            "bw_titel" => $request->request->get("bw_titel")
        );
        $formData = array(
            "alias" => $request->request->get("alias"),
            "formID" => $request->request->get("formID")
        );
        $files = array(
            "anschreiben" => $request->files->get("anschreiben"),
            "lebenslauf" => $request->files->get("lebenslauf"),
            "zeugnisse" => $request->files->get("zeugnisse")
        );
        if ($formData['formID'] == 'bewerbung') {
            $this->_addCandidatesLogic->addCandidate($submittedData, $formData, $files);
        }
        return new Response("Hello world");
    }

    public function onAddCandidate(array $submittedData, array $formData, ?array $files): void
    {
        /*  if ($formData['formID'] == 'bewerbung')
        {
        $this->_addCandidatesLogic->addCandidate($submittedData, $formData, $files);
        }*/
    }
}