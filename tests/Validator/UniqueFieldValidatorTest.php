<?php

namespace App\Tests\Validator;

use App\Entity\User;
use App\Validator\UniqueField;
use App\Validator\UniqueFieldValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class UniqueFieldValidatorTest extends ConstraintValidatorTestCase

{
    protected function createValidator(): ConstraintValidatorInterface
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        return new UniqueFieldValidator($entityManager);
    }

    public function testNullIsValid(): void
    {
        $this->validator->validate(null, new UniqueField(User::class, 'email'));

        $this->assertNoViolation();
    }

    public function testEmptyStringIsValid(): void
    {
        $this->validator->validate('', new UniqueField(User::class, 'email'));

        $this->assertNoViolation();
    }
}
