<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\FormBundle\EventListener;

use Mautic\CoreBundle\Helper\BuilderTokenHelperFactory;
use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use Mautic\FormBundle\FormEvents;
use Mautic\FormBundle\Model\FormModel;
use Mautic\PageBundle\Event\PageBuilderEvent;
use Mautic\PageBundle\Event\PageDisplayEvent;
use Mautic\PageBundle\PageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

class PageSubscriber implements EventSubscriberInterface
{
    private $formRegex = '{form=(.*?)}';

    /**
     * @var FormModel
     */
    private $formModel;

    /**
     * @var BuilderTokenHelperFactory
     */
    private $builderTokenHelperFactory;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var CorePermissions
     */
    private $security;

    /**
     * PageSubscriber constructor.
     */
    public function __construct(
        FormModel $formModel,
        BuilderTokenHelperFactory $builderTokenHelperFactory,
        TranslatorInterface $translator,
        CorePermissions $security
    ) {
        $this->formModel                 = $formModel;
        $this->builderTokenHelperFactory = $builderTokenHelperFactory;
        $this->translator                = $translator;
        $this->security                  = $security;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PageEvents::PAGE_ON_DISPLAY => ['onPageDisplay', 0],
            PageEvents::PAGE_ON_BUILD   => ['onPageBuild', 0],
        ];
    }

    /**
     * Add forms to available page tokens.
     */
    public function onPageBuild(PageBuilderEvent $event)
    {
        if ($event->abTestWinnerCriteriaRequested()) {
            //add AB Test Winner Criteria
            $formSubmissions = [
                'group'    => 'mautic.form.abtest.criteria',
                'label'    => 'mautic.form.abtest.criteria.submissions',
                'event'    => FormEvents::ON_DETERMINE_SUBMISSION_RATE_WINNER,
            ];
            $event->addAbTestWinnerCriteria('form.submissions', $formSubmissions);
        }

        if ($event->tokensRequested($this->formRegex)) {
            $tokenHelper = $this->builderTokenHelperFactory->getBuilderTokenHelper('form');
            $event->addTokensFromHelper($tokenHelper, $this->formRegex, 'name');
        }
    }

    public function onPageDisplay(PageDisplayEvent $event)
    {
        $content = $event->getContent();
        $page    = $event->getPage();
        $regex   = '/'.$this->formRegex.'/i';

        preg_match_all($regex, $content, $matches);

        if (count($matches[0])) {
            foreach ($matches[1] as $id) {
                $form = $this->formModel->getEntity($id);
                if (null !== $form &&
                    (
                        $form->isPublished(false) ||
                        $this->security->hasEntityAccess(
                            'form:forms:viewown', 'form:forms:viewother', $form->getCreatedBy()
                        )
                    )
                ) {
                    $formHtml = ($form->isPublished()) ? $this->formModel->getContent($form) :
                        '<div class="mauticform-error">'.
                        $this->translator->trans('mautic.form.form.pagetoken.notpublished').
                        '</div>';

                    //add the hidden page input
                    $pageInput = "\n<input type=\"hidden\" name=\"mauticform[mauticpage]\" value=\"{$page->getId()}\" />\n";
                    $formHtml  = preg_replace('#</form>#', $pageInput.'</form>', $formHtml);

                    //pouplate get parameters
                    $this->formModel->populateValuesWithGetParameters($form, $formHtml);
                    $content = str_replace('{form='.$id.'}', $formHtml, $content);
                } else {
                    $content = str_replace('{form='.$id.'}', '', $content);
                }
            }
        }
        $event->setContent($content);
    }
}
