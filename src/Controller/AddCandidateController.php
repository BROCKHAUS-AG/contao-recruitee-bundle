<?php

namespace BrockhausAg\ContaoRecruiteeBundle\Controller;

use BrockhausAg\ContaoRecruiteeBundle\Logic\AddCandidatesLogicRoute;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use BrockhausAg\ContaoRecruiteeBundle\Logic\LoadJsonJobsLogic;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AddCandidateController
 *
 * @Route("/recruitee/sentApplication",
 *     name="brockhaus_ag_contao_recruitee_add_candidate",
 *     defaults={
 *         "_scope" = "frontend",
 *         "_token_check" = true
 *     }
 * )
 */
class AddCandidateController extends AbstractController
{
    private AddCandidatesLogicRoute $_addCandidatesLogic;

    public function __construct(LoggerInterface $logger, string $path)
    {
        $this->_addCandidatesLogic = new AddCandidatesLogicRoute($logger, $path);
    }


    public function __invoke(Request $request): Response
    {
        if (!$request->request->get("redirectPage")) {
            return new Response("Fehler ungültige Anfrage");
        }
        if($this->isInputValid($request)) {
            if (str_contains($request->request->get("redirectPage"), "https://")) {
                $url = $request->request->get("redirectPage");
            } else {
                $url = "https://" . $request->getHost() . "/". $request->request->get("redirectPage");
            }
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
                "zeugnisse" => $request->files->get("zeugnisse"),
                "foto" => $request->files->get("foto"),
                "videobewerbung" => $request->files->get("videobewerbung")
            );
            if ($formData['formID'] == 'bewerbung') {
                $this->_addCandidatesLogic->addCandidate($submittedData, $formData, $files);
            }
            return new RedirectResponse($url);
        } else {
            return new Response("Fehler ungültige Formulareingabe");
        }
    }

    private function isInputValid(Request $input) : bool {
        return $input->request->get("spam") == $input->request->get("spamKey");
    }
}