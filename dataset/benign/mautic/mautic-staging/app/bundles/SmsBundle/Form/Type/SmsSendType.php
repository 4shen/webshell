<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\SmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class SmsSendType.
 */
class SmsSendType extends AbstractType
{
    /**
     * @var RouterInterface
     */
    protected $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'sms',
            SmsListType::class,
            [
                'label'      => 'mautic.sms.send.selectsmss',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                    'tooltip'  => 'mautic.sms.choose.smss',
                    'onchange' => 'Mautic.disabledSmsAction()',
                ],
                'multiple'    => false,
                'required'    => true,
                'constraints' => [
                    new NotBlank(
                        ['message' => 'mautic.sms.choosesms.notblank']
                    ),
                ],
            ]
        );

        if (!empty($options['update_select'])) {
            $windowUrl = $this->router->generate(
                'mautic_sms_action',
                [
                    'objectAction' => 'new',
                    'contentOnly'  => 1,
                    'updateSelect' => $options['update_select'],
                ]
            );

            $builder->add(
                'newSmsButton',
                ButtonType::class,
                [
                    'attr' => [
                        'class'   => 'btn btn-primary btn-nospin',
                        'onclick' => 'Mautic.loadNewWindow({
                        "windowUrl": "'.$windowUrl.'"
                    })',
                        'icon' => 'fa fa-plus',
                    ],
                    'label' => 'mautic.sms.send.new.sms',
                ]
            );

            $sms = $options['data']['sms'];

            // create button edit sms
            $windowUrlEdit = $this->router->generate(
                'mautic_sms_action',
                [
                    'objectAction' => 'edit',
                    'objectId'     => 'smsId',
                    'contentOnly'  => 1,
                    'updateSelect' => $options['update_select'],
                ]
            );

            $builder->add(
                'editSmsButton',
                ButtonType::class,
                [
                    'attr' => [
                        'class'    => 'btn btn-primary btn-nospin',
                        'onclick'  => 'Mautic.loadNewWindow(Mautic.standardSmsUrl({"windowUrl": "'.$windowUrlEdit.'"}))',
                        'disabled' => !isset($sms),
                        'icon'     => 'fa fa-edit',
                    ],
                    'label' => 'mautic.sms.send.edit.sms',
                ]
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['update_select']);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'smssend_list';
    }
}
