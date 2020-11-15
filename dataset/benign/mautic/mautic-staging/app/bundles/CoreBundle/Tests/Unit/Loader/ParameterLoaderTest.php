<?php

/*
 * @copyright   2020 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CoreBundle\Tests\Unit\Loader;

use Mautic\CoreBundle\Loader\ParameterLoader;
use PHPUnit\Framework\TestCase;

class ParameterLoaderTest extends TestCase
{
    public function testParametersAreLoaded(): void
    {
        $envParameters = json_encode(['default_daterange_filter' => '-1 day']);
        putenv('MAUTIC_CONFIG_PARAMETERS='.$envParameters);

        $loader = new ParameterLoader(__DIR__.'/TestRoot');
        $loader->loadIntoEnvironment();

        $parameterBag = $loader->getParameterBag();

        $this->assertEquals('https://language-packs.mautic.com/', $parameterBag->get('translations_fetch_url'));
        $this->assertEquals('https://language-packs.mautic.com/', getenv('MAUTIC_TRANSLATIONS_FETCH_URL'));

        $this->assertEquals('foobar.com', $parameterBag->get('mailer_host'));
        $this->assertEquals('foobar.com', getenv('MAUTIC_MAILER_HOST'));

        $this->assertEquals('-1 day', $parameterBag->get('default_daterange_filter'));
        $this->assertEquals('-1 day', getenv('MAUTIC_DEFAULT_DATERANGE_FILTER'));

        putenv('MAUTIC_CONFIG_PARAMETERS=');
    }
}
