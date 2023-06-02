<?php

namespace App\Tests;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

trait WebTestTrait
{
    private const SAVE_BUTTON = 'Enregistrer';

    public static function getService(string $service): object
    {
        return static::getContainer()->get($service) ?? throw new \LogicException(sprintf('Service %s not found.', $service));
    }

    public static function getTestUser(): User
    {
        /** @var UserRepository $userRepository */
        $userRepository = self::getService(UserRepository::class);

        /** @var User */
        return $userRepository->findOneBy(['email' => 'test.user@example.com']);
    }

    public static function getTranslator(): TranslatorInterface
    {
        return self::getService(TranslatorInterface::class);
    }

    public static function getTranslatedActionText(string $actionName, string $entityName): string
    {
        $translatedButton = self::getTranslator()->trans('actions.' . $actionName);
        return self::replaceDynamicEntityValueInTranslatedText($translatedButton, $entityName);
    }

    public static function getTranslatedSuccessMessage(string $actionName, string $entityName, array $arrayDynamicText): string
    {
        $translatedSuccessMessage = static::getTranslator()->trans('success.' . $actionName);
        $translatedSuccessMessage = str_replace('%entity%', $entityName, $translatedSuccessMessage);

        return sprintf($translatedSuccessMessage, ...$arrayDynamicText);
    }

    private static function replaceDynamicEntityValueInTranslatedText(string $translatedText, string $entityValue): string
    {
        return str_replace('%entity%', $entityValue, $translatedText);
    }
}
