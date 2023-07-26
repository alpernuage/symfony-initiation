<?php

namespace App\Domain;

use App\Domain\User\UserInput;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

trait UniqueEmailTrait
{
    public function __construct(private readonly UserRepository $userRepository, private readonly TranslatorInterface $uniqueEmailTranslator)
    {
    }

    function isEmailUnique(FormInterface $form, UserInput $userInput): bool
    {
        $existingUser = $this->userRepository->findByEmail(['email' => $userInput->email]);

        if ($existingUser) {
            $message = $this->uniqueEmailTranslator->trans('constraints.unique_email_value', ['%value%' => $userInput->email]);
            $form->get('email')->addError(new FormError($message));

            return false;
        }

        return true;
    }

    public function setTranslatorForUniqueEmailMessage(TranslatorInterface $uniqueEmailTranslator): void
    {
        $this->translator = $uniqueEmailTranslator;
    }
}
