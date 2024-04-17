<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('getCustomers')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups('getCustomers')]
    #[Assert\NotBlank(message: "Le nom du customer est obligatoire. ")]
    #[Assert\Length(min: 1, max: 255, minMessage: "Le nom doit faire au moins {{ limit }} caractère", maxMessage: "Le nom ne peut pas faire plus de {{ limit }} caractères")]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'customer')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups('getProducts')]
    #[Assert\NotNull(message: "Le produit est obligatoire. ")]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'customer')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le partner est obligatoire. ")]
    private ?Partner $partner = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getPartner(): ?Partner
    {
        return $this->partner;
    }

    public function setPartner(?Partner $partner): static
    {
        $this->partner = $partner;

        return $this;
    }
}
