<?php

namespace App\Entity;

use App\Repository\RegistrationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RegistrationRepository::class)]
class Registration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 31)]
    private ?string $event = null;

    #[ORM\Column(length: 127)]
    private ?string $helloasso_ticket = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'registrations')]
    #[ORM\JoinColumn(name: 'fk_user', referencedColumnName: 'id', nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getEvent(): ?string
    {
        return $this->event;
    }

    public function setEvent(string $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getHelloassoTicket(): ?string
    {
        return $this->helloasso_ticket;
    }

    public function setHelloassoTicket(string $helloasso_ticket): static
    {
        $this->helloasso_ticket = $helloasso_ticket;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }
}
