<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\EventListener;

use Doctrine\ORM\ORMException;
use Mautic\EmailBundle\Form\Type\EmailSendType;
use Mautic\EmailBundle\Form\Type\FormSubmitActionUserEmailType;
use Mautic\EmailBundle\Model\EmailModel;
use Mautic\FormBundle\Event\FormBuilderEvent;
use Mautic\FormBundle\Event\SubmissionEvent;
use Mautic\FormBundle\FormEvents;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Tracker\ContactTracker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FormSubscriber implements EventSubscriberInterface
{
    /**
     * @var EmailModel
     */
    private $emailModel;

    /**
     * @var ContactTracker
     */
    private $contactTracker;

    public function __construct(
        EmailModel $emailModel,
        ContactTracker $contactTracker
    ) {
        $this->emailModel     = $emailModel;
        $this->contactTracker = $contactTracker;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::FORM_ON_BUILD            => ['onFormBuilder', 0],
            FormEvents::ON_EXECUTE_SUBMIT_ACTION => [
                ['onFormSubmitActionSendEmail', 0],
            ],
        ];
    }

    /**
     * Add a send email actions to available form submit actions.
     */
    public function onFormBuilder(FormBuilderEvent $event)
    {
        $event->addSubmitAction('email.send.user', [
            'group'             => 'mautic.email.actions',
            'label'             => 'mautic.email.form.action.sendemail.admin',
            'description'       => 'mautic.email.form.action.sendemail.admin.descr',
            'formType'          => FormSubmitActionUserEmailType::class,
            'formTheme'         => 'MauticEmailBundle:FormTheme\EmailSendList',
            'eventName'         => FormEvents::ON_EXECUTE_SUBMIT_ACTION,
            'allowCampaignForm' => true,
        ]);

        $event->addSubmitAction('email.send.lead', [
            'group'           => 'mautic.email.actions',
            'label'           => 'mautic.email.form.action.sendemail.lead',
            'description'     => 'mautic.email.form.action.sendemail.lead.descr',
            'formType'        => EmailSendType::class,
            'formTypeOptions' => ['update_select' => 'formaction_properties_email'],
            'formTheme'       => 'MauticEmailBundle:FormTheme\EmailSendList',
            'eventName'       => FormEvents::ON_EXECUTE_SUBMIT_ACTION,
        ]);
    }

    /**
     * @throws ORMException
     */
    public function onFormSubmitActionSendEmail(SubmissionEvent $event): void
    {
        if (false === $event->checkContext('email.send.user') && false === $event->checkContext('email.send.lead')) {
            return;
        }

        $properties = $event->getAction()->getProperties();
        $emailId    = isset($properties['useremail']) ? (int) $properties['useremail']['email'] : (int) $properties['email'];
        $email      = $this->emailModel->getEntity($emailId);

        if (null === $email || false === $email->isPublished()) {
            return;
        }

        $currentLead = $this->getCurrentLead($event->getActionFeedback());

        if (isset($properties['user_id']) && $properties['user_id']) {
            $this->emailModel->sendEmailToUser($email, $properties['user_id'], $currentLead, $event->getTokens());
        } elseif (isset($currentLead['email'])) {
            $this->emailModel->sendEmail($email, $currentLead, [
                'source'    => ['form', $event->getAction()->getForm()->getId()],
                'tokens'    => $event->getTokens(),
                'ignoreDNC' => true,
            ]);
        }
    }

    private function getCurrentLead(array $feedback): ?array
    {
        // Deal with Lead email
        if (!empty($feedback['lead.create']['lead'])) {
            // the lead was just created via the lead.create action
            $currentLead = $feedback['lead.create']['lead'];
        } else {
            $currentLead = $this->contactTracker->getContact();
        }

        if ($currentLead instanceof Lead) {
            $currentLead = $currentLead->getProfileFields();
        }

        return $currentLead;
    }
}
