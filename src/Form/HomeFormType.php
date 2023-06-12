<?php

namespace App\Form;

use App\Domain\Home\HomeInput;
use App\Entity\User;
use App\EventSubscriber\HomeSubscriber;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HomeFormType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HomeInput::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('address', TextType::class, [
                'label' => 'home.address',
            ])
            ->add('city', TextType::class, [
                'label' => 'home.city',
            ])
            ->add('zipCode', TextType::class, [
                'label' => 'home.zip_code',
            ])
            ->add('country', CountryType::class, [
                'label' => 'home.country',
            ])
            ->add('currentlyOccupied', CheckboxType::class, [
                'label' => 'home.currently_occupied',
                'required' => false,
            ])
            ->add('user', EntityType::class, [
                'label' => 'labels.user',
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getFirstName() . ' ' . $user->getLastName();
                },
            ])
            ->add('save', SubmitType::class, ['label' => 'buttons.save'])
            ->addEventSubscriber(new HomeSubscriber());
    }
}
