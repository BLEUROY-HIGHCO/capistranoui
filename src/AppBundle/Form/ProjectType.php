<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
            ])
            ->add('folder', TextType::class, [
                'required' => true,
                'attr' => [
                    'readonly' => true
                ],
            ])
            ->add('githubOwner', TextType::class, [
                'required' => true,
                'attr' => [
                    'readonly' => true
                ],
            ])
            ->add('githubProject', TextType::class, [
                'required' => true,
                'attr' => [
                    'readonly' => true
                ],
            ])
            ->add('thumbFile', FileType::class, [
                'required' => false,
            ])
            ->add('environments', CollectionType::class, [
                'entry_type'   => EnvironmentType::class,
                'by_reference' => false,
                'allow_add'    => true,
                'delete_empty' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Project',
        ]);
    }

}
