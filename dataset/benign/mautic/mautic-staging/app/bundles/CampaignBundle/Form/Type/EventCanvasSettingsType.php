<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CampaignBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class EventCanvasSettingsType.
 */
class EventCanvasSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('droppedX', HiddenType::class);

        $builder->add('droppedY', HiddenType::class);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'campaignevent_canvassettings';
    }
}
