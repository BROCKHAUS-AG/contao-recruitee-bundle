<?php


/*
 * This file is part of Contao Recruitee Bundle.
 *
 * (c) BROCKHAUS AG 2021 <info@brockhaus-ag.de>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/brockhaus-ag/contao-recruitee-bundle
 */

declare(strict_types=1);

namespace BrockhausAg\ContaoRecruiteeBundle\Tests\ContaoManager;

use BrockhausAg\ContaoRecruiteeBundle\BrockhausAgContaoRecruiteeBundle;
use BrockhausAg\ContaoRecruiteeBundle\ContaoManager\Plugin;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\DelegatingParser;
use Contao\TestCase\ContaoTestCase;

/**
 * Class PluginTest
 *
 * @package BrockhausAg\ContaoRecruiteeBundle\Tests\ContaoManager
 */
class PluginTest extends ContaoTestCase
{
    public function testInstantiation(): void
    {
        $this->assertInstanceOf(Plugin::class, new Plugin());
    }

    public function testGetBundles(): void
    {
        $plugin = new Plugin();

        $bundles = $plugin->getBundles(new DelegatingParser());

        $this->assertCount(1, $bundles);
        $this->assertInstanceOf(BundleConfig::class, $bundles[0]);
        $this->assertSame(BrockhausAgContaoRecruiteeBundle::class, $bundles[0]->getName());
        $this->assertSame([ContaoCoreBundle::class], $bundles[0]->getLoadAfter());
    }
}
