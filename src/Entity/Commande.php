<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message:"Veuillez saisir la date.")]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[Assert\NotBlank(message:"Veuillez saisir le montant totale.")]
    #[ORM\Column]
    private ?float $totale = null;

    #[Assert\NotBlank(message:"Veuillez saisir le statut.")]
    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    #[Assert\NotBlank(message:"Veuillez choisir le produit.")]
    #[ORM\ManyToOne(inversedBy: 'commandes')]
    private ?Produit $produits = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getTotale(): ?float
    {
        return $this->totale;
    }

    public function setTotale(float $totale): static
    {
        $this->totale = $totale;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getProduits(): ?Produit
    {
        return $this->produits;
    }

    public function setProduits(?Produit $produits): static
    {
        $this->produits = $produits;

        return $this;
    }
}
