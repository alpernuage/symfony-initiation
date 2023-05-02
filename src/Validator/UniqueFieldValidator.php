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

    public function validate($value, Constraint $constraint): void
    {
        /* @var App\Validator\UniqueField $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        $fieldName = $constraint->field;
        $fieldValue = $value->$fieldName;

        $entityRepository = $this->entityManager->getRepository($constraint->entityClass);
        $entity = $entityRepository->findOneBy([$fieldName => $fieldValue]);

        if ($entity && $entity !== $value) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $fieldValue)
                ->atPath($fieldName)
                ->addViolation();
        }
    }
}
