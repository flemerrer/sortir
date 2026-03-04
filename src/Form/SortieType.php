<?php

    namespace App\Form;

    use App\Entity\Lieu;
    use App\Entity\Site;
    use App\Entity\Ville;
    use App\Models\SortieDTO;
    use Symfony\Bridge\Doctrine\Form\Type\EntityType;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
    use Symfony\Component\Form\Extension\Core\Type\HiddenType;
    use Symfony\Component\Form\Extension\Core\Type\IntegerType;
    use Symfony\Component\Form\Extension\Core\Type\TextareaType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\Form\FormEvent;
    use Symfony\Component\Form\FormEvents;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class SortieType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $minDate = new \DateTime();
            $builder
                ->add('id', HiddenType::class, ['required' => false])
                ->add('nom', TextType::class, ['attr' => ['class' => 'form-control mb-2']])
                ->add('dateHeureDebut', DateTimeType::class, [
                    'widget' => 'single_text',
                    'data' => $minDate,
                    'attr' => ['min' => $minDate->format('Y-m-d'), 'class' => 'form-control mb-2']
                ])
                ->add('duree', IntegerType::class, [
                    'label' => "Durée (en minutes)",
                    'data' => 15,
                    'attr' => ['min' => 15,'class' => 'form-control mb-2']
                ])
                ->add('dateLimiteInscription', DateTimeType::class, [
                    'widget' => 'single_text',
                    'data' => $minDate,
                    'attr' => ['min' => $minDate->format('Y-m-d'), 'class' => 'form-control mb-2']
                ])
                ->add('nbInscriptionsMax', IntegerType::class, [
                    'label' => "Places disponibles",
                    'attr' => ['min' => 1, 'class' => 'form-control mb-2']
                ])
                ->add('infosSortie', TextareaType::class, [
                    'required' => false,
                    'attr' => ['class' => 'form-control mb-2']
                ])
                ->add('site', EntityType::class, [
                    'label' => "Site organisateur",
                    'class' => Site::class,
                    'required' => false,
                    'choice_value' => 'id',
                    'choice_label' => 'getNom',
                    'placeholder' => false,
                    'attr' => ['class' => 'form-control mb-2']
                ])
                ->add('lieuxDisponibles', EntityType::class, [
                    'label' => "Choisir un lieu (ou en créer un)*",
                    'class' => Lieu::class,
                    'required' => false,
                    'choice_value' => 'id',
                    'choice_label' => 'getNomVille',
                    'placeholder' => 'Choisir un lieu',
                    'attr' => ['class' => 'location-select form-control mb-2']
                ])
                ->add('villesDisponibles', EntityType::class, [
                    'label' => "Ville*",
                    'class' => Ville::class,
                    'required' => false,
                    'choice_label' => 'getNom',
                    'placeholder' => 'Choisir une ville',
                    'attr' => ['class' => 'form-control mb-2']
                ])
                ->add('nomNouveauLieu', TextType::class, [
                    'required' => false,
                    'label' => 'Nom*',
                    'attr' => ['class' => 'form-control mb-2']
                ])
                ->add('rueNouveauLieu', TextType::class, [
                    'required' => false,
                    'label' => 'Rue*',
                    'attr' => ['class' => 'form-control mb-2']
                ])
                ->add('nouveauLieuLatitude', TextType::class, [
                    'required' => false,
                    'label' => 'Latitude*',
                    'attr' => ['class' => 'form-control mb-2']
                ])
                ->add('nouveauLieuLongitude', TextType::class, [
                    'required' => false,
                    'label' => 'Longitude*',
                    'attr' => ['class' => 'form-control mb-2']
                ])
            ;

            $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $form->getData();

                $hasLieu = !empty($data->lieuxDisponibles);
                $hasAllNewLieuFields = !empty($data->nomNouveauLieu) &&
                                      !empty($data->rueNouveauLieu) &&
                                      !empty($data->villesDisponibles) &&
                                      !empty($data->nouveauLieuLatitude) &&
                                      !empty($data->nouveauLieuLongitude);

                if (!$hasLieu && !$hasAllNewLieuFields) {
                    $form->get('lieuxDisponibles')->addError(new \Symfony\Component\Form\FormError(
                        "Choisissez un lieu existant ou remplissez tous les champs pour créer un nouveau lieu."
                    ));
                }
            });
        }

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'data_class' => SortieDTO::class,
            ]);
        }
    }
