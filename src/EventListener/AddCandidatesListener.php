<?php

declare(strict_types=1);

/*
 * This file is part of Contao Recruitee Bundle.
 *
 * (c) BROCKHAUS AG 2021 <info@brockhaus-ag.de>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/brockhaus-ag/contao-recruitee-bundle
 */

namespace BrockhausAg\ContaoRecruiteeBundle\EventListener;

use BrockhausAg\ContaoRecruiteeBundle\Logic\AddCandidatesLogic;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class AddCandidatesListener
{
    private AddCandidatesLogic $_addCandidatesLogic;

    public function __construct(LoggerInterface $logger, string $path)
    {
        $this->_addCandidatesLogic = new AddCandidatesLogic($logger, $path);
    }

    /**
     * @Hook("processFormData")
     */
    public function onAddCandidate(array $submittedData, array $formData, ?array $files) : void
    {
        if ($formData['formID'] == 'bewerbung')
        {
            $this->_addCandidatesLogic->addCandidate($submittedData, $formData, $files);
        }
    }
}