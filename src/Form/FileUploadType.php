<?php

namespace App\Form;

use App\Entity\UserFileUpdloadRecord;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class FileUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('csv', FileType::class, [
            'mapped' => false,
            'required' => true,
            'constraints' => [
                new File(
                    maxSize: '1024k',
                    //fixme: doesn't work, don't know why...
                    mimeTypes: ['text/csv', 'application/csv', 'text/x-comma-separated-values', 'text/x-csv', 'text/plain'],
                    mimeTypesMessage:  'Veuillez charger un fichier CSV valide.',
                    extensions: ['csv'],
                    extensionsMessage: 'Veuillez charger un fichier CSV valide.'
                )
            ]
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            UserFileUpdloadRecord::class
        ]);
    }
}
