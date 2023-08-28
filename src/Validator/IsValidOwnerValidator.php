<?php

namespace App\Validator;

use App\ApiResource\UserApi;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsValidOwnerValidator extends ConstraintValidator
{
    public function __construct(private Security $security)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        assert($constraint instanceof IsValidOwner);

        if (null === $value || '' === $value) {
            return;
        }

        // constraint is only meant to be used above a User property
        assert($value instanceof UserApi);

        $user = $this->security->getUser();
        if (!$user) {
            throw new \LogicException('IsOwnerValidator should only be used when a user is logged in.');
        }
        assert($user instanceof User);

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        if ($value->id !== $user->getId()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
