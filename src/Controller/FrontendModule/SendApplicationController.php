<?php
    namespace BrockhausAg\ContaoRecruiteeBundle\Controller\FrontendModule;

    use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
    use Contao\CoreBundle\Exception\RedirectResponseException;
    use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
    use Contao\ModuleModel;
    use Contao\PageModel;
    use Contao\Template;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;

    /**
     * @FrontendModule(category="miscellaneous")
     */
    class SendApplicationController extends AbstractFrontendModuleController
    {
        protected function sendApplication(Template $template, ModuleModel $model, Request $request){
            var_dump($request);
            file_put_contents("/var/www/html/contao/test.xml", "moin moin2121");
            if ($request->isMethod('post')) {
                if (null !== ($redirectPage = PageModel::findByPk($model->jumpTo))) {
                    throw new RedirectResponseException($redirectPage->getAbsoluteUrl());
                }
            }

            $template->action = $request->getUri();

            return $template->getResponse();
        }

        protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
        {
            //die("send app one");
            var_dump($request);
            file_put_contents("/var/www/html/contao/test.xml", "moin moin2121");
            if ($request->isMethod('post')) {
                //die("SEND APPLICATION!");
                /*
                if ($formData['formID'] == 'bewerbung')
                {
                    $this->_addCandidatesLogic->addCandidate($submittedData, $formData, $files);
                }
                */
            }

            $template->action = $request->getUri();

            return $template->getResponse();
        }
    }

