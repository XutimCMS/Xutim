<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Validator;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Webmozart\Assert\Assert;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Util\FileHasher;

class UniqueFileValidator extends ConstraintValidator
{
    public function __construct(private readonly FileRepository $repo)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueFile) {
            throw new UnexpectedTypeException($constraint, UniqueFile::class);
        }

        if (!$value instanceof UploadedFile) {
            return;
        }

        $mimeType = $value->getMimeType();
        Assert::string($mimeType);
        $isImage = str_starts_with($mimeType, 'image/');

        $hash = $isImage ?
        FileHasher::genereatePerceptualHash($value->getPathname()) :
        FileHasher::generateSHA256Hash($value->getPathname());

        if ($this->repo->findOneBy(['hash' => $hash]) !== null) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
