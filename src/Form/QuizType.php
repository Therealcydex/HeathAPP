<?php

namespace App\Form;

use App\Entity\Quiz;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Enum\QuizTypeEnum;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class QuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('type', ChoiceType::class, [
                'label' => 'Quiz Type',
                'choices' => QuizTypeEnum::cases(), // ✅ Fetch enum cases directly
                'choice_label' => fn (QuizTypeEnum $choice) => $choice->name, // ✅ Display enum name
                'choice_value' => fn (?QuizTypeEnum $choice) => $choice?->value, // ✅ Store enum value
                'attr' => ['class' => 'form-select'],
                'placeholder' => 'Select a type',
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quiz::class,
        ]);
    }
}
