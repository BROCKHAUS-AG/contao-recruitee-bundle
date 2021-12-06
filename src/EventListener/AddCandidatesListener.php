<?php

declare(strict_types=1);

/*
 * This file is part of Contao Microsoft SSO Bundle.
 *
 * (c) BROCKHAUS AG 2021 <info@brockhaus-ag.de>
 * Author Niklas Lurse (INoTime) <nlurse@brockhaus-ag.de>
 *
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/BROCKHAUS-AG/contao-microsoft-sso-bundle
 */

namespace BrockhausAg\ContaoRecruiteeBundle\EventListener;

use BrockhausAg\ContaoRecruiteeBundle\Logic\AddCandidatesLogic;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Psr\Log\LoggerInterface;

class AddCandidatesListener
{
    private AddCandidatesLogic $_addCandidatesLogic;

    public function __construct(LoggerInterface $logger)
    {
        $this->_addCandidatesLogic = new AddCandidatesLogic($logger);
    }

    /**
     * @Hook("processFormData")
     */
    public function onAddCandidate(array $formData, array $submittedData, ?array $files) : void
    {
        if ($formData['formID'] == 'bewerbung')
        {
            $this->_addCandidatesLogic->addCandidate($formData, $submittedData, $files);
        }
    }
}