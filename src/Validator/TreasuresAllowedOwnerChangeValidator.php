<?php

namespace App\Validator;

use App\ApiResource\DragonTreasureApi;
use App\ApiResource\UserApi;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TreasuresAllowedOwnerChangeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        assert($constraint instanceof TreasuresAllowedOwnerChange);

        if (null === $value || '' === $value) {
            return;
        }

        assert($value instanceof UserApi);

        foreach ($value->dragonTreasures as $dragonTreasureApi) {
            assert($dragonTreasureApi instanceof DragonTreasureApi);

            $originalOwnerId = $dragonTreasureApi->owner?->id;
            $newOwnerId = $value->id;

            if (!$originalOwnerId || $originalOwnerId === $newOwnerId) {
                return;
            }

            // the owner is being changed
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
