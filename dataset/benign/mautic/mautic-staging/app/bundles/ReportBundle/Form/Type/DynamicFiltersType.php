<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\ReportBundle\Form\Type;

use Mautic\CoreBundle\Form\Type\ButtonGroupType;
use Mautic\ReportBundle\Entity\Report;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DynamicFiltersType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['report']->getFilters() as $filter) {
            if (isset($filter['dynamic']) && 1 === $filter['dynamic']) {
                $column     = $filter['column'];
                $definition = $options['filterDefinitions']->definitions[$column];
                $args       = [
                    'label'      => $definition['label'],
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'    => 'form-control',
                        'onchange' => "Mautic.filterTableData('report.".$options['report']->getId()."','".$column."',this.value,'list','.report-content');",
                    ],
                    'required' => false,
                ];

                switch ($definition['type']) {
                    case 'bool':
                    case 'boolean':
                        $type            = ButtonGroupType::class;
                        $args['choices'] = [
                            [
                                'mautic.core.form.no'      => false,
                                'mautic.core.form.yes'     => true,
                                'mautic.core.filter.clear' => '2',
                            ],
                        ];

                        if (isset($options['data'][$definition['alias']])) {
                            $args['data'] = (1 == (int) $options['data'][$definition['alias']]);
                        } else {
                            $args['data'] = (int) $filter['value'];
                        }
                        break;
                    case 'date':
                        $type           = DateType::class;
                        $args['input']  = 'string';
                        $args['widget'] = 'single_text';
                        $args['format'] = 'y-MM-dd';
                        $args['attr']['class'] .= ' datepicker';
                        break;
                    case 'datetime':
                        $type           = DateTimeType::class;
                        $args['input']  = 'string';
                        $args['widget'] = 'single_text';
                        $args['format'] = 'y-MM-dd HH:mm:ss';
                        $args['attr']['class'] .= ' datetimepicker';
                        break;
                    case 'multiselect':
                    case 'select':
                        $type            = ChoiceType::class;
                        $args['choices'] = array_flip($definition['list']);
                        break;
                    default:
                        $type = TextType::class;
                        break;
                }

                $builder->add($definition['alias'], $type, $args);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'report_dynamicfilters';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'filterDefinitions' => [],
                'report'            => new Report(),
            ]
        );
    }
}
