<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic, Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\IntegrationsBundle\Form\Type;

use Mautic\CoreBundle\Form\Type\FormButtonsType;
use Mautic\CoreBundle\Form\Type\YesNoButtonGroupType;
use Mautic\IntegrationsBundle\Exception\IntegrationNotFoundException;
use Mautic\IntegrationsBundle\Helper\ConfigIntegrationsHelper;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormAuthInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormFeaturesInterface;
use Mautic\PluginBundle\Entity\Integration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IntegrationConfigType extends AbstractType
{
    /**
     * @var ConfigIntegrationsHelper
     */
    private $integrationsHelper;

    /**
     * IntegrationConfigType constructor.
     */
    public function __construct(ConfigIntegrationsHelper $integrationsHelper)
    {
        $this->integrationsHelper = $integrationsHelper;
    }

    /**
     * @throws IntegrationNotFoundException
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $integrationObject = $this->integrationsHelper->getIntegration($options['integration']);

        // isPublished
        $builder->add(
            'isPublished',
            YesNoButtonGroupType::class,
            [
                'label'      => 'mautic.integration.enabled',
                'label_attr' => ['class' => 'control-label'],
            ]
        );

        // apiKeys
        if ($integrationObject instanceof ConfigFormAuthInterface) {
            $builder->add(
                'apiKeys',
                $integrationObject->getAuthConfigFormName(),
                [
                    'label'       => false,
                    'integration' => $integrationObject,
                ]
            );
        }

        // supportedFeatures
        if ($integrationObject instanceof ConfigFormFeaturesInterface) {
            // @todo add tooltip support
            $builder->add(
                'supportedFeatures',
                ChoiceType::class,
                [
                    'label'      => 'mautic.integration.features',
                    'label_attr' => ['class' => 'control-label'],
                    'choices'    => array_flip($integrationObject->getSupportedFeatures()),
                    'expanded'   => true,
                    'multiple'   => true,
                    'required'   => false,
                ]
            );
        }

        // featureSettings
        $builder->add(
            'featureSettings',
            IntegrationFeatureSettingsType::class,
            [
                'label'             => false,
                'integrationObject' => $integrationObject,
            ]
        );

        $builder->add('buttons', FormButtonsType::class);

        $builder->setAction($options['action']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(
            [
                'integration',
            ]
        );

        $resolver->setDefined(
            [
                'data_class'  => Integration::class,
            ]
        );
    }
}
