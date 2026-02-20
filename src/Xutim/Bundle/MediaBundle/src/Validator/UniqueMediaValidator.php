<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Validator;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Webmozart\Assert\Assert;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\MediaBundle\Util\FileHasher;

class UniqueMediaValidator extends ConstraintValidator
{
    public function __construct(private readonly MediaRepositoryInterface $repo)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueMedia) {
            throw new UnexpectedTypeException($constraint, UniqueMedia::class);
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

        if ($this->repo->findByHash($hash) !== null) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
