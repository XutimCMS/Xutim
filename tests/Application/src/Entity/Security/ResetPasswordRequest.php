<?php

declare(strict_types=1);

namespace App\Entity\Security;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Xutim\SecurityBundle\Domain\Model\ResetPasswordRequest as BaseResetPasswordRequest;

#[Entity]
#[Table(name: 'app_reset_password_request')]
class ResetPasswordRequest extends BaseResetPasswordRequest
{
}
