<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('getProducts')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups('getProducts')]
    private ?string $title = null;

    #[ORM\Column]
    #[Groups('getProducts')]
    private ?float $price = null;

    #[ORM\OneToMany(targetEntity: Customer::class, mappedBy: 'product', orphanRemoval: true)]
    private Collection $customer;

    public function __construct()
    {
        $this->customer = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection<int, Customer>
     */
    public function getCustomer(): Collection
    {
        return $this->customer;
    }

    public function addCustomer(Customer $customer): static
    {
        if (!$this->customer->contains($customer)) {
            $this->customer->add($customer);
            $customer->setProduct($this);
        }

        return $this;
    }

    public function removeCustomer(Customer $customer): static
    {
        if ($this->customer->removeElement($customer)) {
            // set the owning side to null (unless already changed)
            if ($customer->getProduct() === $this) {
                $customer->setProduct(null);
            }
        }

        return $this;
    }
}
