<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Model;

use Symfony\Component\Uid\Uuid;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface as BaseResetPasswordRequestInterface;

interface ResetPasswordRequestInterface extends BaseResetPasswordRequestInterface
{
    public function getId(): Uuid;

    public function getUser(): UserInterface;
}
