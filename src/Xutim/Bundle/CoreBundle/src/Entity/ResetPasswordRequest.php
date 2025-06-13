<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Uid\Uuid;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestTrait;
use Xutim\CoreBundle\Domain\Model\ResetPasswordRequestInterface as XutimResetPasswordRequestInterface;
use Xutim\CoreBundle\Domain\Model\UserInterface;

#[MappedSuperclass]
class ResetPasswordRequest implements ResetPasswordRequestInterface, XutimResetPasswordRequestInterface
{
    use ResetPasswordRequestTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
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
