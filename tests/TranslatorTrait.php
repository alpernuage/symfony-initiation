<?php

namespace App\Tests;

use Symfony\Contracts\Translation\TranslatorInterface;

trait TranslatorTrait
{
    use WebTestTrait;

    public static function getTranslator(): TranslatorInterface
    {
        /** @var TranslatorInterface */
        return self::getService(TranslatorInterface::class);
    }

    public static function getTranslatedActionText(string $actionName, string $entityName, string $locale): string
    {
        $translatedAction = self::getTranslator()->trans('actions.' . $actionName, locale: $locale);

        return self::replaceDynamicEntityValueInTranslatedText($translatedAction, $entityName);
    }

    /**
     * @param string[] $arrayDynamicText
     */
    public static function getTranslatedSuccessMessage(string $actionName, string $entityName, array $arrayDynamicText, string $locale): string
    {
        $translatedSuccessMessage = static::getTranslator()->trans('success.' . $actionName, locale: $locale);
        $translatedSuccessMessage = str_replace('%entity%', $entityName, $translatedSuccessMessage);

        return sprintf($translatedSuccessMessage, ...$arrayDynamicText);
    }

    private static function replaceDynamicEntityValueInTranslatedText(string $translatedText, string $entityValue): string
    {
        return str_replace('%entity%', $entityValue, $translatedText);
    }

    public static function getTranslatedEntityName(string $locale, string $entityName): string
    {
        return self::getTranslator()->trans('labels.' . $entityName, locale: $locale);
    }
}
