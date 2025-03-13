<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Random\RandomException;

class RandomStringGenerator
{
    /**
     * @param int<1,max> $bytesLen
     * @throws RandomException
     */
    public function generateRandomString(int $bytesLen): string
    {
        $bytes = random_bytes($bytesLen);

        return bin2hex($bytes);
    }
}
