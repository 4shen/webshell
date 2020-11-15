<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Form\Type;

use Mautic\CoreBundle\Form\Validator\Constraints\FileEncoding as EncodingValidation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class LeadImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'file',
            FileType::class,
            [
                'label' => 'mautic.lead.import.file',
                'attr'  => [
                    'accept' => '.csv',
                    'class'  => 'form-control',
                ],
                'constraints' => [
                    new File(
                        [
                            'mimeTypes'        => ['text/csv', 'text/plain'],
                            'mimeTypesMessage' => 'mautic.core.invalid_file_type',
                        ]
                    ),
                    new EncodingValidation(
                        [
                            'encodingFormat'        => ['UTF-8'],
                            'encodingFormatMessage' => 'mautic.core.invalid_file_encoding',
                        ]
                    ),
                ],
                'error_bubbling' => true,
            ]
        );

        $constraints = [
            new \Symfony\Component\Validator\Constraints\NotBlank(
                ['message' => 'mautic.core.value.required']
            ),
        ];

        $default = (empty($options['data']['delimiter'])) ? ',' : htmlspecialchars($options['data']['delimiter']);
        $builder->add(
            'delimiter',
            TextType::class,
            [
                'label' => 'mautic.lead.import.delimiter',
                'attr'  => [
                    'class' => 'form-control',
                ],
                'data'        => $default,
                'constraints' => $constraints,
            ]
        );

        $default = (empty($options['data']['enclosure'])) ? '&quot;' : htmlspecialchars($options['data']['enclosure']);
        $builder->add(
            'enclosure',
            TextType::class,
            [
                'label' => 'mautic.lead.import.enclosure',
                'attr'  => [
                    'class' => 'form-control',
                ],
                'data'        => $default,
                'constraints' => $constraints,
            ]
        );

        $default = (empty($options['data']['escape'])) ? '\\' : $options['data']['escape'];
        $builder->add(
            'escape',
            TextType::class,
            [
                'label' => 'mautic.lead.import.escape',
                'attr'  => [
                    'class' => 'form-control',
                ],
                'data'        => $default,
                'constraints' => $constraints,
            ]
        );

        $default = (empty($options['data']['batchlimit'])) ? 100 : (int) $options['data']['batchlimit'];
        $builder->add(
            'batchlimit',
            TextType::class,
            [
                'label' => 'mautic.lead.import.batchlimit',
                'attr'  => [
                    'class'   => 'form-control',
                    'tooltip' => 'mautic.lead.import.batchlimit_tooltip',
                ],
                'data'        => $default,
                'constraints' => $constraints,
            ]
        );

        $builder->add(
            'start',
            SubmitType::class,
            [
                'attr' => [
                    'class'   => 'btn btn-primary',
                    'icon'    => 'fa fa-upload',
                    'onclick' => "mQuery(this).prop('disabled', true); mQuery('form[name=\'lead_import\']').submit();",
                ],
                'label' => 'mautic.lead.import.upload',
            ]
        );

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'lead_import';
    }
}
