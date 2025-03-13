<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;
use Xutim\CoreBundle\Kernel;

require __DIR__ . '/../vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__ . '/../.env');

$kernel = new Kernel('test', true);
$kernel->boot();

return $kernel->getContainer()->get('test.service_container')->get('twig');
