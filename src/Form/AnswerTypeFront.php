<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Answer;

class AnswerTypeFront extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('answers', ChoiceType::class, [
                'choices' => $options['answers'], 
                'choice_label' => function ($choice) {
                    return $choice->getText(); 
                },
                'choice_value' => 'id', 
                'expanded' => true, 
                'multiple' => true, 
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null, 
            'answers' => [], 
        ]);
    }
}
