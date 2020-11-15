<?php

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\PageBundle\EventListener;

use Mautic\CoreBundle\Event\DetermineWinnerEvent;
use Mautic\PageBundle\Entity\HitRepository;
use Mautic\PageBundle\PageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

class DetermineWinnerSubscriber implements EventSubscriberInterface
{
    /**
     * @var HitRepository
     */
    private $hitRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(HitRepository $hitRepository, TranslatorInterface $translator)
    {
        $this->hitRepository = $hitRepository;
        $this->translator    = $translator;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            PageEvents::ON_DETERMINE_BOUNCE_RATE_WINNER => ['onDetermineBounceRateWinner', 0],
            PageEvents::ON_DETERMINE_DWELL_TIME_WINNER  => ['onDetermineDwellTimeWinner', 0],
        ];
    }

    /**
     * Determines the winner of A/B test based on bounce rates.
     */
    public function onDetermineBounceRateWinner(DetermineWinnerEvent $event)
    {
        //find the hits that did not go any further
        $parent    = $event->getParameters()['parent'];
        $children  = $event->getParameters()['children'];
        $pageIds   = $parent->getRelatedEntityIds();
        $startDate = $parent->getVariantStartDate();

        if (null != $startDate && !empty($pageIds)) {
            //get their bounce rates
            $counts = $this->hitRepository->getBounces($pageIds, $startDate, true);
            if ($counts) {
                // Group by translation
                $combined = [
                    $parent->getId() => $counts[$parent->getId()],
                ];

                if ($parent->hasTranslations()) {
                    $translations = $parent->getTranslationChildren()->getKeys();

                    foreach ($translations as $translation) {
                        $combined[$parent->getId()]['bounces'] += $counts[$translation]['bounces'];
                        $combined[$parent->getId()]['totalHits'] += $counts[$translation]['totalHits'];
                        $combined[$parent->getId()]['rate'] = ($combined[$parent->getId()]['totalHits']) ? round(
                            ($combined[$parent->getId()]['bounces'] / $combined[$parent->getId()]['totalHits']) * 100,
                            2
                        ) : 0;
                    }
                }

                foreach ($children as $child) {
                    $combined[$child->getId()] = $counts[$child->getId()];

                    if ($child->hasTranslations()) {
                        $translations              = $child->getTranslationChildren()->getKeys();
                        foreach ($translations as $translation) {
                            $combined[$child->getId()]['bounces'] += $counts[$translation]['bounces'];
                            $combined[$child->getId()]['totalHits'] += $counts[$translation]['totalHits'];
                            $combined[$child->getId()]['rate'] = ($combined[$child->getId()]['totalHits']) ? round(
                                ($combined[$child->getId()]['bounces'] / $combined[$child->getId()]['totalHits']) * 100,
                                2
                            ) : 0;
                        }
                    }
                }
                unset($counts);

                //let's arrange by rate
                $rates             = [];
                $support['data']   = [];
                $support['labels'] = [];
                $bounceLabel       = $this->translator->trans('mautic.page.abtest.label.bounces');

                foreach ($combined as $pid => $stats) {
                    $rates[$pid]                     = $stats['rate'];
                    $support['data'][$bounceLabel][] = $rates[$pid];
                    $support['labels'][]             = $pid.':'.$stats['title'];
                }

                // investigate the rate calculation, seems that lowest value should be the winner
                $max                   = max($rates);
                $support['step_width'] = (ceil($max / 10) * 10);

                $winners = ($max > 0) ? array_keys($rates, $max) : [];

                $event->setAbTestResults([
                    'winners'         => $winners,
                    'support'         => $support,
                    'basedOn'         => 'page.bouncerate',
                    'supportTemplate' => 'MauticPageBundle:SubscribedEvents\AbTest:bargraph.html.php',
                ]);

                return;
            }
        }

        $event->setAbTestResults([
            'winners' => [],
            'support' => [],
            'basedOn' => 'page.bouncerate',
        ]);
    }

    /**
     * Determines the winner of A/B test based on dwell time rates.
     */
    public function onDetermineDwellTimeWinner(DetermineWinnerEvent $event)
    {
        //find the hits that did not go any further
        $parent    = $event->getParameters()['parent'];
        $pageIds   = $parent->getRelatedEntityIds();
        $startDate = $parent->getVariantStartDate();

        if (null != $startDate && !empty($pageIds)) {
            //get their bounce rates
            $counts  = $this->hitRepository->getDwellTimesForPages($pageIds, ['fromDate' => $startDate]);
            $support = [];

            if ($counts) {
                //in order to get a fair grade, we have to compare the averages here since a page that is only shown
                //25% of the time will have a significantly lower sum than a page shown 75% of the time
                $avgs              = [];
                $support['data']   = [];
                $support['labels'] = [];
                foreach ($counts as $pid => $stats) {
                    $avgs[$pid]                                                                                = $stats['average'];
                    $support['data'][$this->translator->trans('mautic.page.abtest.label.dewlltime.average')][] = $stats['average'];
                    $support['labels'][]                                                                       = $pid.':'.$stats['title'];
                }

                //set max for scales
                $max                   = max($avgs);
                $support['step_width'] = (ceil($max / 10) * 10);

                //get the page ids with the greatest average dwell time
                $winners = ($max > 0) ? array_keys($avgs, $max) : [];

                $event->setAbTestResults([
                    'winners'         => $winners,
                    'support'         => $support,
                    'basedOn'         => 'page.dwelltime',
                    'supportTemplate' => 'MauticPageBundle:SubscribedEvents\AbTest:bargraph.html.php',
                ]);

                return;
            }
        }

        $event->setAbTestResults([
            'winners' => [],
            'support' => [],
            'basedOn' => 'page.dwelltime',
        ]);
    }
}
