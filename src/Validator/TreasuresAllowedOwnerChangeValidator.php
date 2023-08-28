<?php

namespace App\Validator;

use App\Entity\DragonTreasure;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TreasuresAllowedOwnerChangeValidator extends ConstraintValidator
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        assert($constraint instanceof TreasuresAllowedOwnerChange);

        if (null === $value || '' === $value) {
            return;
        }

        return;

        $unitOfWork = $this->entityManager->getUnitOfWork();
        foreach ($value as $dragonTreasure) {
            assert($dragonTreasure instanceof DragonTreasure);

            $originalData = $unitOfWork->getOriginalEntityData($dragonTreasure);
            $originalOwnerId = $originalData['owner_id'];
            $newOwnerId = $dragonTreasure->getOwner()->getId();

            if (!$originalOwnerId || $originalOwnerId === $newOwnerId) {
                return;
            }

            // the owner is being changed
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
