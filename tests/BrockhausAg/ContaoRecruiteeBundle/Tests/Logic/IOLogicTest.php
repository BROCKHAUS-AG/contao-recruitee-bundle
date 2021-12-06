<?php

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

declare(strict_types=1);

namespace BrockhausAg\ContaoRecruiteeBundle\Tests\Logic;

use BrockhausAg\ContaoRecruiteeBundle\Logic\IOLogic;
use Contao\TestCase\ContaoTestCase;
use Psr\Log\LoggerInterface;

/**
 * Class IOLogicTest
 *
 * @package BrockhausAg\ContaoRecruiteeBundle\Tests\Logic
 */
class IOLogicTest extends ContaoTestCase
{
    private LoggerInterface $loggerMock;

    public function setUp(): void
    {
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
    }

    public function testInstantiation(): void
    {
        $this->assertInstanceOf(IOLogic::class,
            new IOLogic($this->loggerMock));
    }
}
