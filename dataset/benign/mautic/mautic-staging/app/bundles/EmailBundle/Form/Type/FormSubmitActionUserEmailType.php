<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Form\Type;

use Mautic\UserBundle\Form\Type\UserListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormSubmitActionUserEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('useremail',
            EmailSendType::class,
            [
                'label' => 'mautic.email.emails',
                'attr'  => [
                    'class'   => 'form-control',
                    'tooltip' => 'mautic.email.choose.emails_descr',
                ],
                'update_select' => 'formaction_properties_useremail_email',
            ]
        );

        $builder->add(
            'user_id',
            UserListType::class,
            [
                'label'      => 'mautic.email.form.users',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mautic.core.help.autocomplete',
                ],
                'required' => false,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => false,
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'email_submitaction_useremail';
    }
}
