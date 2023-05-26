<?php

namespace App\Validator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueFieldValidator extends ConstraintValidator
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (null === $value || '' === $value || !$constraint instanceof UniqueField) {
            return;
        }

        $fieldName = $constraint->field;
        $fieldValue = strval($value->$fieldName);

        $entityRepository = $this->entityManager->getRepository($constraint->entityClass);
        $entity = $entityRepository->findOneBy([$fieldName => $fieldValue]);

        if ($entity !== null && $entity->getId() !== $value->getId()) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $fieldValue)
                ->atPath($fieldName)
                ->addViolation();
        }
    }
}
