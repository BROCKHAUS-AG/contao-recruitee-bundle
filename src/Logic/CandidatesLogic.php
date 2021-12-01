<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoRecruiteeBundle\Logic;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as TwigEnvironment;

class CandidatesLogic
{
    private $twig;

    public function __construct(TwigEnvironment $twig)
    {
        $this->twig = $twig;
    }


    private function sendToApi($arrPost, $arrForm, $arrFiles)
    {
        /*$page = $arrForm['alias'] == 'bewerbung-experts' ? 'experts' : 'spaceitup';

        if ($arrForm['formID'] == 'bewerbung') {

            $offerId = $arrPost["jobID"];
            $source = "";

            $anrede = $arrPost["bw_anrede"];
            $titel = $arrPost["bw_titel"];
            $vorname = $arrPost["bw_vorname"];
            $nachname = $arrPost["bw_name"];
            $geburtsdatum = $arrPost["bw_geburtsdatum"];
            $geburtsort = $arrPost["bw_geburtsort"];
            $strasse = $arrPost["bw_strasse"];
            $ort = $arrPost["bw_ort"];
            $email = $arrPost["bw_email"];
            $telefon = $arrPost["bw_telefon"];
            $mobil = $arrPost["bw_mobil"];
            $message = $arrPost["profil_sonstiges"];
            $github = $arrPost["github"];
            $linkedin = $arrPost["linkedin"];
            $xing = $arrPost["xing"];

            $anschreiben = $arrFiles["anschreiben"];
            $lebenslauf = $arrFiles["lebenslauf"];
            $zeugnis = $arrFiles["zeugnisse"];
            $foto = $arrFiles["foto"];

            $additionalSource = $arrPost["bw_quelle"];


            $this->CreateNewCandidate($page, $offerId, $source, $anrede, $titel, $vorname, $nachname, $geburtsdatum,
                $geburtsort, $strasse, $ort, $email, $telefon, $mobil, $message, $github, $linkedin, $xing,
                $anschreiben, $lebenslauf, $zeugnis, $foto, $additionalSource);

            $response_code = 200;
            $response_message = '<h1 style="text-align: center;" class="ok">Ihre Bewerbung wurde versendet.</h1>';


            $_SESSION['coveto_response_code'] = $response_code;
            $_SESSION['coveto_response_message'] = $response_message;

        }*/
    }


    public function addCandidate($_POST, $_FILES)
    {

    }
}