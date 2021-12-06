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

namespace BrockhausAg\ContaoRecruiteeBundle\Tests\Controller;

use BrockhausAg\ContaoRecruiteeBundle\Controller\LoadJobsController;
use Contao\TestCase\ContaoTestCase;
use Psr\Log\LoggerInterface;
use Twig\Environment as TwigEnvironment;

/**
 * Class LoadJobsControllerTest
 *
 * @package BrockhausAg\ContaoRecruiteeBundle\Tests\Controller
 */
class LoadJobsControllerTest extends ContaoTestCase
{
    private TwigEnvironment $twigMock;
    private LoggerInterface $loggerMock;

    public function setUp(): void
    {
        $this->twigMock = $this->getMockBuilder(TwigEnvironment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
    }

    public function testInstantiation(): void
    {
        $this->assertInstanceOf(LoadJobsController::class,
            new LoadJobsController($this->twigMock, $this->loggerMock));
    }
}
