<?php

namespace App\Tests\Validator;

use App\Entity\User;
use App\Validator\UserUniqueEmail;
use App\Validator\UserUniqueEmailValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class UserUniqueEmailValidatorTest extends ConstraintValidatorTestCase

{
    protected function createValidator(): ConstraintValidatorInterface
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        return new UserUniqueEmailValidator($entityManager);
    }

    public function testNullIsValid(): void
    {
        $this->validator->validate(null, new UserUniqueEmail(User::class, 'email'));

        $this->assertNoViolation();
    }

    public function testEmptyStringIsValid(): void
    {
        $this->validator->validate('', new UserUniqueEmail(User::class, 'email'));

        $this->assertNoViolation();
    }
}
