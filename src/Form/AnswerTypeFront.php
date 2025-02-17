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
                'choices' => $options['answers'], // ✅ Pass answers dynamically
                'choice_label' => function ($choice) {
                    return $choice->getText(); // ✅ Show the answer text
                },
                'choice_value' => 'id',  // ✅ Store answer ID
                'expanded' => true,  // ✅ Display as checkboxes
                'multiple' => true,  // ✅ Allow multiple selections
            ]);
            // ->add('submit', SubmitType::class, [
            //     'label' => 'Next',
            //     'attr' => ['class' => 'btn btn-gradient-primary'],
            // ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null, // ✅ No binding to an entity
            'answers' => [], // ✅ Default to empty list
        ]);
    }
}
