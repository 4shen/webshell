<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\ReportBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mautic\CoreBundle\Helper\CsvHelper;
use Mautic\CoreBundle\Helper\Serializer;
use Mautic\ReportBundle\Entity\Report;

class LoadReportData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $reports = CsvHelper::csv_to_array(__DIR__.'/fakereportdata.csv');

        foreach ($reports as $count => $rows) {
            $report = new Report();
            $key    = $count + 1;
            foreach ($rows as $col => $val) {
                if ('NULL' != $val) {
                    $setter = 'set'.ucfirst($col);
                    if (in_array($col, ['columns', 'filters', 'graphs', 'tableOrder'])) {
                        $val = Serializer::decode(stripslashes($val));
                    }
                    $report->$setter($val);
                }
            }

            $manager->persist($report);

            $this->setReference('report-'.$key, $report);
        }
        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 5;
    }
}
