<?php

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\PluginBundle\Event;

use Mautic\PluginBundle\Integration\UnifiedIntegrationInterface;
use Symfony\Component\Form\FormBuilder;

class PluginIntegrationFormBuildEvent extends AbstractPluginIntegrationEvent
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var FormBuilder
     */
    private $builder;

    public function __construct(UnifiedIntegrationInterface $integration, FormBuilder $builder, array $options)
    {
        $this->integration = $integration;
        $this->builder     = $builder;
        $this->options     = $options;
    }

    /**
     * @return FormBuilder
     */
    public function getFormBuilder()
    {
        return $this->builder;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
