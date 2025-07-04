<?php

declare(strict_types=1);


namespace Xutim\SecurityBundle\Security;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Uid\Uuid;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface as ModelResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestTrait;
use Xutim\SecurityComponent\Domain\Model\ResetPasswordRequestInterface;

#[MappedSuperclass]
class ResetPasswordRequest implements ResetPasswordRequestInterface, ModelResetPasswordRequestInterface
{
    use ResetPasswordRequestTrait;

    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[ManyToOne()]
    #[JoinColumn(nullable: false)]
    private UserInterface $user;

    public function __construct(UserInterface $user, \DateTimeInterface $expiresAt, string $selector, string $hashedToken)
    {
        $this->id = Uuid::v4();
        $this->user = $user;
        $this->initialize($expiresAt, $selector, $hashedToken);
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
