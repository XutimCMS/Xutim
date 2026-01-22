<?php

declare(strict_types=1);

namespace App\Entity\Redirect;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Xutim\RedirectBundle\Domain\Model\Redirect as BaseRedirect;

#[Entity]
#[Table(name: 'app_redirect')]
class Redirect extends BaseRedirect
{
}
