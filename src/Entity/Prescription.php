<?php

namespace App\Entity;

use App\Repository\PrescriptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrescriptionRepository::class)]
class Prescription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $medicalDetails = null;

    #[ORM\Column(length: 255)]
    private ?string $doctorNotes = null;

    #[ORM\ManyToOne(inversedBy: 'prescriptions')]
    private ?Appointment $appointment = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMedicalDetails(): ?string
    {
        return $this->medicalDetails;
    }

    public function setMedicalDetails(string $medicalDetails): static
    {
        $this->medicalDetails = $medicalDetails;

        return $this;
    }

    public function getDoctorNotes(): ?string
    {
        return $this->doctorNotes;
    }

    public function setDoctorNotes(string $doctorNotes): static
    {
        $this->doctorNotes = $doctorNotes;

        return $this;
    }

    public function getAppointment(): ?Appointment
    {
        return $this->appointment;
    }

    public function setAppointment(?Appointment $appointment): static
    {
        $this->appointment = $appointment;

        return $this;
    }
}
