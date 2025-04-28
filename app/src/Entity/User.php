<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 127)]
    private ?string $name = null;

    #[ORM\Column(length: 127)]
    private ?string $firstname = null;

    #[ORM\Column(length: 127, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private ?bool $droitImage = false;

    #[ORM\Column(length: 127)]
    private ?string $phone = null;

    #[ORM\Column(length: 256)]
    private ?string $social = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Character::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $characters;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Registration::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $registrations;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: EmergencyContact::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $emergencyContacts;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Allergy::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $allergies;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Note::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $notes;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ResetPasswordRequest::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $resetPasswordRequests;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $passwordRequestedAt = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $resetToken = null;

    public function __construct()
    {
        $this->characters = new ArrayCollection();
        $this->registrations = new ArrayCollection();
        $this->emergencyContacts = new ArrayCollection();
        $this->allergies = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->resetPasswordRequests = new ArrayCollection();
        $this->roles = [RoleType::ROLE_USER];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
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

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getFullName(): string
    {
        return $this->firstname . ' ' . $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getDroitImage(): ?bool
    {
        return $this->droitImage;
    }

    public function setDroitImage(bool $droitImage): self
    {
        $this->droitImage = $droitImage;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getSocial(): ?string
    {
        return $this->social;
    }

    public function setSocial(string $social): static
    {
        $this->social = $social;
        return $this;
    }

    /**
     * @return Collection<int, Character>
     */
    public function getCharacters(): Collection
    {
        return $this->characters;
    }

    public function hasMainCharacter(): bool
    {
        return $this->characters->filter(function (Character $character) {
            return $character->isMain();
        })->count() > 0;
    }

    public function getMainCharacter(): ?Character
    {
        return $this->characters->filter(function (Character $character) {
            return $character->isMain();
        })->first();
    }

    public function hasSecondaryCharacter(): bool
    {
        return $this->characters->filter(function (Character $character) {
            return $character->isSecondary();
        })->count() > 0;
    }

    public function getSecondaryCharacter(): ?Character
    {
        return $this->characters->filter(function (Character $character) {
            return !$character->isSecondary();
        })->first();
    }

    /**
     * @return Collection<int, Registration>
     */
    public function getRegistrations($includeHidden = false): Collection
    {
        if ($includeHidden) {
            return $this->registrations;
        }
        return $this->registrations->filter(function (Registration $registration) {
            return $registration->getEventType()->getStatus() !== EventType::STATUS_HIDDEN;
        });
    }

    public function lastRegistrationWithState(string $state): ?Registration
    {
        return $this->registrations->filter(function (Registration $registration) use ($state) {
            return $registration->getEventType()->getStatus() === $state;
        })->last() ?: null;
    }

    /**
     * @return Collection<int, EmergencyContact>
     */
    public function getEmergencyContacts(): Collection
    {
        return $this->emergencyContacts;
    }

    public function addEmergencyContact(EmergencyContact $contact): self
    {
        if (!$this->emergencyContacts->contains($contact)) {
            $this->emergencyContacts->add($contact);
            $contact->setUser($this);
        }

        return $this;
    }

    public function removeEmergencyContact(EmergencyContact $contact): self
    {
        if ($this->emergencyContacts->removeElement($contact)) {
            if ($contact->getUser() === $this) {
                $contact->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Allergy>
     */
    public function getAllergies(): Collection
    {
        return $this->allergies;
    }

    public function addAllergy(Allergy $allergy): self
    {
        if (!$this->allergies->contains($allergy)) {
            $this->allergies->add($allergy);
            $allergy->setUser($this);
        }

        return $this;
    }

    public function removeAllergy(Allergy $allergy): self
    {
        if ($this->allergies->removeElement($allergy)) {
            if ($allergy->getUser() === $this) {
                $allergy->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Note>
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes->add($note);
            $note->setUser($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->notes->removeElement($note)) {
            if ($note->getUser() === $this) {
                $note->setUser(null);
            }
        }

        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getPasswordRequestedAt(): ?\DateTimeInterface
    {
        return $this->passwordRequestedAt;
    }

    public function setPasswordRequestedAt(?\DateTimeInterface $passwordRequestedAt): self
    {
        $this->passwordRequestedAt = $passwordRequestedAt;
        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    public function isPasswordRequestNonExpired(int $ttl): bool
    {
        if (!$this->passwordRequestedAt) {
            return false;
        }

        $requestedAt = clone $this->passwordRequestedAt;
        if ($requestedAt instanceof \DateTime) {
            $requestedAt = $requestedAt->modify('+' . $ttl . ' hours');
        }

        return $requestedAt > new \DateTime();
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }

    public function addRole(string $role): self
    {
        if (!$this->hasRole($role)) {
            $this->roles[] = $role;
        }
        return $this;
    }

    public function removeRole(string $role): self
    {
        if ($this->hasRole($role)) {
            $this->roles = array_diff($this->roles, [$role]);
        }
        return $this;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(RoleType::ROLE_ADMIN);
    }

    public function isOrga(): bool
    {
        return $this->hasRole(RoleType::ROLE_ORGA);
    }
}
