<?php

namespace Wallabag\CoreBundle\Event\Subscriber;

use Lexik\Bundle\FormFilterBundle\Event\GetFilterConditionEvent;
use Lexik\Bundle\FormFilterBundle\Event\Subscriber\DoctrineORMSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This custom class override the default behavior of LexikFilterBundle on `filter_date_range`
 * It converts a date_range to date_time_range to add hour to be able to grab a whole day (from 00:00:00 to 23:59:59).
 */
class CustomDoctrineORMSubscriber extends DoctrineORMSubscriber implements EventSubscriberInterface
{
    public function filterDateRange(GetFilterConditionEvent $event)
    {
        $expr = $event->getFilterQuery()->getExpressionBuilder();
        $values = $event->getValues();
        $value = $values['value'];

        // left date should start at midnight
        if (isset($value['left_date'][0]) && $value['left_date'][0] instanceof \DateTime) {
            $value['left_date'][0]->setTime(0, 0, 0);
        }

        // right adte should end one second before midnight
        if (isset($value['right_date'][0]) && $value['right_date'][0] instanceof \DateTime) {
            $value['right_date'][0]->setTime(23, 59, 59);
        }

        if (isset($value['left_date'][0]) || isset($value['right_date'][0])) {
            $event->setCondition($expr->dateTimeInRange($event->getField(), $value['left_date'][0], $value['right_date'][0]));
        }
    }
}
