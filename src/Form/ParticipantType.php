<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class, [
                'attr' => ['class' => 'form-control mb-2'],
                'label' => 'Pseudo'
                ])
            ->add('nom', TextType::class, [
                'attr' => ['class' => 'form-control mb-2'],
                'label' => 'Nom'
                ])
            ->add('prenom', TextType::class, [
                'attr' => ['class' => 'form-control mb-2'],
                'label' => 'Prénom'
                ])
            ->add('telephone', TextType::class, [
                'attr' => ['class' => 'form-control mb-2'],
                'label' => 'Téléphone'
                ])
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control mb-2'],
                'label' => 'Adresse mail'
            ])
            ->add('motdepasse', PasswordType::class, [
                'mapped' => false,
                'label' => 'Mot de passe',
                'attr' => ['class' => 'form-control mb-2']
            ])
            ->add('administrateur', CheckboxType::class, [
                'attr' => ['class' => 'form-check-input mb-3'],
                'label' => 'Administrateur',
                'label_attr' => ['class' => 'form-check-label me-2'],
                'required' => false
            ])
            ->add('actif', CheckboxType::class, [
                'attr' => ['class' => 'form-check-input mb-3'],
                'label' => 'Actif',
                'label_attr' => ['class' => 'form-check-label me-2'],
                'required' => false
            ])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'getNom',
                'choice_value' => 'id',
                'attr' => ['class' => 'form-control mb-2'],
                'label' => 'Site'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
