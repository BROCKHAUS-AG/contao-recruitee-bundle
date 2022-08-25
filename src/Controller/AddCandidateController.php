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


    public function __invoke(Request $request) : Response
    {
        $this->createFileIfNotExists("/var/www/html/contao/testing/test.xml");
        $this->createFileIfNotExists("/var/www/html/contao/testing/test2.xml");
        $this->createFileIfNotExists("/var/www/html/contao/testing/test3.xml");
        $files = array($request->files->get('anschreiben'),
            $request->files->get('lebenslauf'),
            $request->files->get('zeugnisse'));
        $formData = $request->request->get("alias");
        file_put_contents("/var/www/html/contao/testing/test.xml", $request->request->get("jobID"));
        file_put_contents("/var/www/html/contao/testing/testk.xml", $request->request->keys());
        file_put_contents("/var/www/html/contao/testing/test1.xml", $request->request->get("bw_anrede"));
        file_put_contents("/var/www/html/contao/testing/test2.xml", $request->request->get("bw_vorname"));
        file_put_contents("/var/www/html/contao/testing/test3.xml", $request->request->get("bw_name"));
        file_put_contents("/var/www/html/contao/testing/test4.xml", $request->request->get("strasse"));
        file_put_contents("/var/www/html/contao/testing/test5.xml", $request->request->get("ort"));
        file_put_contents("/var/www/html/contao/testing/test6.xml", $request->request->get("bw_email"));
        file_put_contents("/var/www/html/contao/testing/test7.xml", $request->request->get("file"));
        file_put_contents("/var/www/html/contao/testing/test8.xml", $request->files->get('anschreiben'));
        file_put_contents("/var/www/html/contao/testing/test9.xml", $request->files->get('lebenslauf'));
        file_put_contents("/var/www/html/contao/testing/test10.xml", $request->files->get('zeugnisse'));
        file_put_contents("/var/www/html/contao/testing/test11.xml", "hi zweiter");
        file_put_contents("/var/www/html/contao/testing/test12.xml", "hi dritter");
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