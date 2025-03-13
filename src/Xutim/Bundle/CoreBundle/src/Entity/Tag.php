<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Repository\TagRepository;

#[Entity(repositoryClass: TagRepository::class)]
class Tag
{
    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[Column(type: 'string', length: 255, unique: true, nullable: false)]
    private string $name;

    public function __construct(string $name)
    {
        $this->id = Uuid::v4();
        $this->name = $name;
    }
}
