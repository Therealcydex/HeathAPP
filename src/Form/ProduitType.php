<?php

namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use DateTime;


class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom',TextType::class, [
                'attr' => [
                    'placeholder' => 'Nom Produit',
                ]
            ])
            ->add('description',TextAreaType::class, [
                'attr' => [
                    'placeholder' => 'Description Produit',
                ]
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Visage' => 'Visage',
                    'Corps' => 'Corps',
                    'Cheveux' => 'Cheveux',
                    'Solaire' => 'Solaire',
                    'Maman & Bébé' => 'Maman & Bébé',
                    'Hygiène' => 'Hygiène',
                    'Homme' => 'Homme',
                ],
                'placeholder' => 'Veuillez choisir le type',
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'html5'=>true,
                'attr'=>['min'=>(new DateTime())->format('Y-m-d')],
            ])
            ->add('prix',TextType::class, [
                'attr' => [
                    'placeholder' => 'Prix Produit',
                ]
            ])
            ->add('image',FileType::class,[
                'label' => 'Choisir une photo',
                'data_class' => null,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
