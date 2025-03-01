<?php
namespace App\Form;

use App\Entity\Appointment;
use App\Entity\Doctor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppointmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('clientName')
            ->add('doctor', EntityType::class, [
                'class' => Doctor::class,
                'choice_label' => function (Doctor $doctor) {
                    return $doctor->getName() . ' - ' . $doctor->getSpecialty();
                },
                'label' => 'Select Doctor',
                'placeholder' => 'Choose a doctor',
                'attr' => ['class' => 'form-control'],
                'choice_attr' => function (Doctor $doctor) {
                    return [
                        'data-picture' => $doctor->getPicture(), // Set the image URL as a data attribute
                    ];
                },
            ])
            ->add('appointmentDate', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
        ]);
    }
}
