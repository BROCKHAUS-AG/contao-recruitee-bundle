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
    private string $requestingServer;

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

        $this->requestingServer = $request->server->get("SERVER_NAME");
        if ($this->isReCaptchaActiveForRequestingWebsite($this->requestingServer)) {
            $captchaResponse = $this->verifyCaptcha($request);
            if(!$captchaResponse->success || $captchaResponse->success && $captchaResponse->score < 0.6) {
                return new Response("ReCaptcha hat die Anfrage als ungültig eingestuft");
            }
        }

        return $this->handleRequest($request);
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

    private function verifyCaptcha(Request $request) {
        $url = "https://www.google.com/recaptcha/api/siteverify";
        $privateToken = $this->ioLogic->getPrivateTokenByServerName($this->requestingServer);
        $fields = [
            "secret" => $privateToken,
            "response" => $request->request->get("spamKey")
        ];
        $fieldsAsString = http_build_query($fields);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $fieldsAsString);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
        return json_decode($result);
    }

    private function isReCaptchaActiveForRequestingWebsite(string $serverName) : bool
    {
        $isActive = false;
        for($index = 0; $index < count($this->websites); $index++) {
            if($this->websites[$index]["serverName"] == $serverName) {
                if($this->websites[$index]["reCaptchaPrivateToken"]) {
                    $isActive = true;
                    break;
                }
            }
        }
        return $isActive;
    }
}