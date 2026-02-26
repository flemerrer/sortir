<?php

    namespace App\Form;

    use App\Entity\Lieu;
    use App\Entity\Ville;
    use App\Models\SortieDTO;
    use Symfony\Bridge\Doctrine\Form\Type\EntityType;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
    use Symfony\Component\Form\Extension\Core\Type\DateType;
    use Symfony\Component\Form\Extension\Core\Type\HiddenType;
    use Symfony\Component\Form\Extension\Core\Type\IntegerType;
    use Symfony\Component\Form\Extension\Core\Type\TextareaType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class SortieType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $minDate = new \DateTime();
            $builder
                ->add('id', HiddenType::class, ['required' => false])
                ->add('nom', TextType::class, [])
                ->add('dateHeureDebut', DateTimeType::class, [
                    'widget' => 'single_text',
                    'data' => $minDate,
                    'attr' => ['min' => $minDate->format('Y-m-d')]
                ])
                ->add('duree', IntegerType::class, [
                    'label' => "Durée (en minutes)",
                    'data' => 15,
                    'attr' => ['min' => 15]
                ])
                ->add('dateLimiteInscription', DateTimeType::class, [
                    'widget' => 'single_text',
                    'data' => $minDate,
                    'attr' => ['min' => $minDate->format('Y-m-d')]
                ])
                ->add('nbInscriptionsMax', IntegerType::class, [
                    'label' => "Places disponibles",
                    'attr' => ['min' => 1]
                ])
                ->add('infosSortie', TextareaType::class, [
                    'required' => false,
                ])
                ->add('lieuxDisponibles', EntityType::class, [
                    'class' => Lieu::class,
                    'required' => false,
                    'choice_value' => 'id',
                    'choice_label' => 'getNomVille',
                    'placeholder' => 'Choisir un lieu',
                    'attr' => ['class' => 'location-select']
                ])
                ->add('villesDisponibles', EntityType::class, [
                    'class' => Ville::class,
                    'required' => false,
                    'choice_label' => 'getNom',
                    'placeholder' => 'Choisir une ville'
                ])
                ->add('nomNouveauLieu', TextType::class, [
                    'required' => false,
                    'label' => 'Nom',
                ])
                ->add('rueNouveauLieu', TextType::class, [
                    'required' => false,
                    'label' => 'Rue',
                ]);
        }

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'data_class' => SortieDTO::class,
            ]);
        }
    }
