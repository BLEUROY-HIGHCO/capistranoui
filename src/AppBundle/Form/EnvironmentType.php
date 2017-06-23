<?php

namespace AppBundle\Form;

use AppBundle\Entity\Project;
use AppBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EnvironmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'attr'     => [
                    'readonly' => true,
                ],
            ])
            ->add('currentVersion', TextType::class, [
                'required' => false,
                'attr'     => [
                    'readonly' => true,
                ],
            ])
            ->add('keepReleases', NumberType::class, [
                'required' => true,
                'attr'     => [
                    'readonly' => true,
                ],
            ])
            ->add('branchSelectable', CheckboxType::class, [
                'required' => false,
                'attr'     => [
                    'readonly' => true,
                ],
            ])
            ->add('defaultBranch', TextType::class, [
                'required' => false,
                'attr'     => [
                    'readonly' => true,
                ],
            ])
            ->add('users', EntityType::class, [
                'class'    => User::class,
                'required' => false,
                'multiple' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Environment',
        ]);
    }
}
