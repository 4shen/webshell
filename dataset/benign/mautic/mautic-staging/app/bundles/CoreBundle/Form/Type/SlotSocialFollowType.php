<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CoreBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SlotSocialFollowType extends SlotType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add(
            'flink',
            TextType::class,
            [
                'label'      => 'mautic.core.facebook.url',
                'label_attr' => ['class' => 'control-label'],
                'required'   => false,
                'attr'       => [
                    'value'           => 'http://www.facebook.com',
                    'class'           => 'form-control',
                    'data-slot-param' => 'flink',
                ],
            ]
        );

        $builder->add(
            'tlink',
            TextType::class,
            [
                'label'      => 'mautic.core.twitter.url',
                'label_attr' => ['class' => 'control-label'],
                'required'   => false,
                'attr'       => [
                    'value'           => 'http://www.twitter.com',
                    'class'           => 'form-control',
                    'data-slot-param' => 'tlink',
                ],
            ]
        );

        $builder->add(
            'align',
            ButtonGroupType::class,
            [
                'label'             => 'mautic.core.image.position',
                'label_attr'        => ['class' => 'control-label'],
                'required'          => false,
                'attr'              => [
                    'class'           => 'form-control',
                    'data-slot-param' => 'align',
                ],
                'choices'           => [
                    'mautic.core.left'   => 0,
                    'mautic.core.center' => 1,
                    'mautic.core.right'  => 2,
                ],
                ]
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'slot_socialfollow';
    }
}
