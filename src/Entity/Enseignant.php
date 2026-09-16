<?php

namespace App\Entity;

use App\Repository\EnseignantRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EnseignantRepository::class)]
class Enseignant
{
    public const AGE_RETRAITE = 64;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    #[Assert\Choice(choices: ['M', 'F'], message: 'Le genre doit être M ou F.')]
    private ?string $genre = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date de naissance est obligatoire.')]
    #[Assert\LessThanOrEqual(value: 'today', message: 'La date de naissance ne peut pas être dans le futur.')]
    private ?\DateTime $dateNaissance = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $lieuNaissance = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $niveauEnseignement = null;

    #[ORM\Column(length: 30)]
    private ?string $statutProfessionnel = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $deletedAt = null;

    #[ORM\ManyToOne(inversedBy: 'enseignants')]
    private ?Localite $localite = null;

    #[Assert\Valid]
    #[ORM\OneToOne(inversedBy: 'enseignant', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\OneToMany(mappedBy: 'enseignant', targetEntity: Affectation::class)]
    private Collection $affectations;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->affectations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(string $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getDateNaissance(): ?\DateTime
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(?\DateTime $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    public function getLieuNaissance(): ?string
    {
        return $this->lieuNaissance;
    }

    public function setLieuNaissance(?string $lieuNaissance): static
    {
        $this->lieuNaissance = $lieuNaissance;

        return $this;
    }

    public function getNiveauEnseignement(): ?string
    {
        return $this->niveauEnseignement;
    }

    public function setNiveauEnseignement(?string $niveauEnseignement): static
    {
        $this->niveauEnseignement = $niveauEnseignement;

        return $this;
    }

    public function getStatutProfessionnel(): ?string
    {
        return $this->statutProfessionnel;
    }

    public function setStatutProfessionnel(string $statutProfessionnel): static
    {
        $this->statutProfessionnel = $statutProfessionnel;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getDeletedAt(): ?\DateTime
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTime $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getLocalite(): ?Localite
    {
        return $this->localite;
    }

    public function setLocalite(?localite $localite): static
    {
        $this->localite = $localite;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Affectation>
     */
    public function getAffectations(): Collection
    {
        return $this->affectations;
    }

    public function addAffectation(Affectation $affectation): static
    {
        if (!$this->affectations->contains($affectation)) {
            $this->affectations->add($affectation);
            $affectation->setEnseignant($this);
        }

        return $this;
    }

    public function removeAffectation(Affectation $affectation): static
    {
        if ($this->affectations->removeElement($affectation)) {
            if ($affectation->getEnseignant() === $this) {
                $affectation->setEnseignant(null);
            }
        }

        return $this;
    }

    public function age(): int
    {
        $aujourdhui = new \DateTimeImmutable();
        $naissance = $this->dateNaissance;

        return $naissance->diff($aujourdhui)->y;
    }

    /**
     * L'enseignant a-t-il atteint l'âge de la retraite ?
     */
    public function estRetraite(): bool
    {
        // 1. Aujourd'hui
        $aujourdhui = new \DateTimeImmutable();

        // 2. La date où il atteindra 64 ans (sa naissance + 64 ans)
        $dateRetraite = \DateTimeImmutable::createFromInterface($this->dateNaissance)
            ->modify('+' . self::AGE_RETRAITE . ' years');

        // 3. Retraité si aujourd'hui est arrivé (au moins) à cette date
        return $aujourdhui >= $dateRetraite;
    }
}
