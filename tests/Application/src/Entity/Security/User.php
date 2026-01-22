<?php

declare(strict_types=1);

namespace App\Entity\Security;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Xutim\SecurityBundle\Domain\Model\User as BaseUser;

#[Entity]
#[Table(name: 'app_user')]
class User extends BaseUser
{
}
