<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Form\Validator\Constraints;

use Mautic\CoreBundle\Helper\UserHelper;
use Mautic\LeadBundle\Entity\LeadListRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

class UniqueUserAliasValidator extends ConstraintValidator
{
    /**
     * @var LeadListRepository
     */
    public $segmentRepository;

    /**
     * @var UserHelper
     */
    public $userHelper;

    public function __construct(LeadListRepository $segmentRepository, UserHelper $userHelper)
    {
        $this->segmentRepository = $segmentRepository;
        $this->userHelper        = $userHelper;
    }

    public function validate($list, Constraint $constraint)
    {
        $field = $constraint->field;

        if (empty($field)) {
            throw new ConstraintDefinitionException('A field has to be specified.');
        }

        if ($list->getAlias()) {
            $lists = $this->segmentRepository->getLists(
                $this->userHelper->getUser(),
                $list->getAlias(),
                $list->getId()
            );

            if (count($lists)) {
                $this->context->buildViolation($constraint->message)
                    ->atPath($field)
                    ->setParameter('%alias%', $list->getAlias())
                    ->addViolation();
            }
        }
    }
}
