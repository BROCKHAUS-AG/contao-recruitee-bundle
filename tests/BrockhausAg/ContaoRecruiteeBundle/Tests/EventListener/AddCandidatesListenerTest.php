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

namespace BrockhausAg\ContaoRecruiteeBundle\Tests\EventListener;

use BrockhausAg\ContaoRecruiteeBundle\EventListener\AddCandidatesListener;
use Contao\TestCase\ContaoTestCase;
use Psr\Log\LoggerInterface;

/**
 * Class AddCandidatesListenerTest
 *
 * @package BrockhausAg\ContaoRecruiteeBundle\Tests\EventListener
 */
class AddCandidatesListenerTest extends ContaoTestCase
{
    private LoggerInterface $loggerMock;

    public function setUp(): void
    {
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
    }

    public function testInstantiation(): void
    {
        $this->assertInstanceOf(AddCandidatesListener::class,
            new AddCandidatesListener($this->loggerMock));
    }
}
