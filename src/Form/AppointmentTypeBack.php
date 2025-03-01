<?php
// src/Form/AppointmentType.php

namespace App\Form;

use App\Entity\Appointment;
use App\Entity\Doctor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class AppointmentTypeBack extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('clientName')
            ->add('appointmentDate', null, [
                'widget' => 'single_text',
            ])
            ->add('doctor', EntityType::class, [ // Use EntityType for the doctor selection
                'class' => Doctor::class,         // Specify the Doctor entity
                'choice_label' => 'name',         // Display doctor's name in the dropdown
                'label' => 'Select Doctor',       // Label for the field
                'placeholder' => 'Choose a doctor', // Optional: A placeholder for the field
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
        ]);
    }
}
