<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Infra\Validator;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\RedirectBundle\Domain\Repository\RedirectRepositoryInterface;
use Xutim\RedirectBundle\Form\RedirectFormData;

class ValidRedirectValidator extends ConstraintValidator
{
    public function __construct(
        private readonly RedirectRepositoryInterface $repo,
        private readonly UrlGeneratorInterface $router
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof RedirectFormData || !$constraint instanceof ValidRedirect) {
            return;
        }

        $source = $value->getSource();
        $target = $this->generateRoute($value->getTargetContentTranslation());

        if ($source === $target) {
            $this->context->buildViolation($constraint->messageSelf)->addViolation();
            return;
        }

        foreach ($this->repo->findAll() as $existing) {
            dump($existing, $value);
            if ($existing->getId() === $value->getId()) {
                continue;
            }

            $existingSource = $existing->getSource();
            $existingTarget = $this->generateRoute($value->getTargetContentTranslation());

            // Loop detection
            if ($existingSource === $target && $existingTarget === $source) {
                $this->context->buildViolation($constraint->messageLoop)
                    ->setParameter('{{ target }}', $target)
                    ->addViolation();
                break;
            }

            // Duplicate source detection
            if ($existingSource === $source) {
                $this->context->buildViolation($constraint->messageDuplicate)
                    ->addViolation();
                break;
            }
        }
    }

    private function generateRoute(ContentTranslationInterface $trans): string
    {
        return $this->router->generate('content_translation_show', [
            'slug' => $trans->getSlug()
        ]);
    }
}
