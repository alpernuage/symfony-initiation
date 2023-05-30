<?php

namespace App\Domain;

use Symfony\Contracts\Translation\TranslatorInterface;

trait SuccessMessageTrait
{
    private TranslatorInterface $translator;

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    protected function getSuccessMessage(string $action, string $entity): string
    {
        return
            $this->translator->trans('success.' . $action, [
                '%entity%' => $this->translator->trans('labels.' . $entity)
            ]);
    }
}
