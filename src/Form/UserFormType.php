<?php

namespace App\Form;

use App\Domain\User\UserInput;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserFormType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserInput::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', null, [
                'label' => 'user.first_name',
            ])
            ->add('lastName', null, [
                'label' => 'user.last_name',
            ])
            ->add('email', null, [
                'label' => 'user.email',
            ])
            ->add('save', SubmitType::class, ['label' => 'buttons.save']);
    }
}
