<?php

namespace BrockhausAg\ContaoRecruiteeBundle\Controller;

use BrockhausAg\ContaoRecruiteeBundle\Logic\AddCandidatesLogicRoute;
use BrockhausAg\ContaoRecruiteeBundle\Logic\IOLogic;
use Couchbase\ReplaceOptions;
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
    private IOLogic $ioLogic;
    private array $websites;
    private string $privateToken;

    private LoggerInterface $logger;
    private string $path;

    public function __construct(LoggerInterface $logger, string $path)
    {
        $this->logger = $logger;
        $this->path = $path;

        $this->ioLogic = new IOLogic($logger, $path);
        $this->websites = $this->ioLogic->loadRecruiteeConfigWebsites();
    }


    public function __invoke(Request $request): Response
    {
        $isTesting = $request->request->get("isTesting");
        if($isTesting == null) {
            $isTesting = "";
        }
        $this->_addCandidatesLogic = new AddCandidatesLogicRoute($this->logger, $this->path, $isTesting);

        $userInput = $request->request->get("spam");
        $actualValue = $request->request->get("spamKey");

        if($actualValue == null || $actualValue == "") {
            return new Response("Fehler leerer captcha code");
        } else {
            if($this->validateCustomCaptcha($userInput, $actualValue)) {
                return $this->handleRequest($request);
            } else {
                return new Response("Fehler falscher captcha code");
            }
        }
    }

    private function handleRequest(Request $request) : Response
    {
        if (!$request->request->get("redirectPage")) {
            return new Response("Fehler ungültige Anfrage");
        }
        if (str_contains($request->request->get("redirectPage"), "https://")) {
            $url = $request->request->get("redirectPage");
        }
        else {
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
            "bw_contactMethod" => $request->request->get("bw_contact"),
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
            "videobewerbung" => $request->files->get("videobewerbung"),
            "foto" => $request->files->get("foto")
        );
        if ($formData['formID'] == 'bewerbung') {
            $this->_addCandidatesLogic->addCandidate($submittedData, $formData, $files);
        }

        return new RedirectResponse($url);
    }

    private function validateCustomCaptcha($userInput, $actualValue) {
        return $userInput == $actualValue;
    }

}