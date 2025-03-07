<?php

namespace BrockhausAg\ContaoRecruiteeBundle\Controller;

use BrockhausAg\ContaoRecruiteeBundle\Logic\AddCandidatesLogicRoute;
use BrockhausAg\ContaoRecruiteeBundle\Logic\IOLogic;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
    private $alternateApplicationURL;

    public function __construct(LoggerInterface $logger, string $path)
    {
        $this->logger = $logger;
        $this->path = $path;

        $this->ioLogic = new IOLogic($logger, $path);
        $this->websites = $this->ioLogic->loadRecruiteeConfigWebsites();
        $this->alternateApplicationURL = $this->ioLogic->loadAlternateApplicationURL();
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
                try {
                    $response = $this->handleRequest($request);
                } catch (\Exception | \Throwable) {
                    return new Response(
                        "Es ist ein Fehler aufgetreten! Bitte schicke deine Bewerbung über <a href='" . $this->alternateApplicationURL . "'>diesen Link</a>",
                        500
                    );
                }
                return $response;
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
            "bw_titel" => $request->request->get("bw_titel"),
        );
        if(!empty($request->request->get("desired_start")) && !empty($request->request->get("desired_end"))) {
            $errorMessage = $this->validateAndAppendInternData($submittedData,
                $request->request->get("desired_start"),
                $request->request->get("desired_end")
            );
            if(!empty($errorMessage)) {
                return new Response($errorMessage);
            }
        }
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

    private function validateAndAppendInternData(&$submittedData, $desiredStart, $desiredEnd): string {
        $errorMessage = $this->validateDates($desiredStart, $desiredEnd);

        if(!empty($errorMessage)) {
            return $errorMessage;
        }

        $submittedData["desired_start"] = $desiredStart;
        $submittedData["desired_end"] = $desiredEnd;

        return "";
    }

    private function validateDates($desiredStart, $desiredEnd): string {
        $_desiredStart = htmlspecialchars(strip_tags($desiredStart));
        $_desiredEnd = htmlspecialchars(strip_tags($desiredEnd));

        if (!$this->validateTimeFormat($desiredStart) || !$this->validateTimeFormat($desiredEnd)) {
            return "Fehler bei der Bewerbung: Bitte geben Sie das Datum im Format YYYY-MM-DD ein.";
        }

        $startTimestamp = strtotime($_desiredStart);
        $endTimestamp = strtotime($_desiredEnd);
        $currentTimestamp = strtotime(date('Y-m-d')); // Get today's date as a timestamp

        if ($startTimestamp < $currentTimestamp)
            return "Fehler bei der Bewerbung: Der Startzeitpunkt darf nicht in der Vergangenheit liegen.";

        if ($startTimestamp > $endTimestamp)
            return "Fehler bei der Bewerbung: Der Startzeitpunkt darf nicht nach dem Endzeitpunkt liegen.";

        return "";
    }

    private function validateTimeFormat($timeString): bool {
        return preg_match("/^\d{4}-\d{2}-\d{2}$/", $timeString);
    }

    private function validateCustomCaptcha($userInput, $actualValue): bool{
        return $userInput == $actualValue;
    }

}