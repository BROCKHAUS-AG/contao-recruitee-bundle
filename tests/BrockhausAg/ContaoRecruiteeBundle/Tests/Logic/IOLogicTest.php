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
            new IOLogic($this->loggerMock, "path"));
    }

    public function testCreatePathWithJobsFile(): void
    {
        $expected = "path/settings/brockhaus-ag/contao-recruitee-bundle/recruiteeJobs.json";
        $ioLogic = new IOLogic($this->loggerMock, "path");

        $actual = $ioLogic->createPathWithJobsFile();

        self::assertSame($expected, $actual);
    }
}
