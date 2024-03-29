<?php

namespace App\Entity;

use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PinRepository;
use App\Entity\Traits\Timestampable;
use Symfony\Component\Validator\Constraints as Assert;



#[ORM\Entity(repositoryClass: PinRepository::class)]
#[ORM\Table(name:"pins")]
#[ORM\HasLifecycleCallbacks]
class Pin
{
    use Timestampable;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"Title Can't be blank")]
    #[Assert\Length(min:3)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(min:10)]
    private ?string $description = null;


    
    #[ORM\Column(length: 255)]
    private ?string $imageName = null;

    #[ORM\ManyToOne(inversedBy: 'pins')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(string $imageName): static
    {
        $this->imageName = $imageName;

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

    public function getUserId():int
    {
        return $this->getUser()->getId();
    }

   
    
}
