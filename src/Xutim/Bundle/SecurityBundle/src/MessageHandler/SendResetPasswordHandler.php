<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\MessageHandler;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Exception\LogicException;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\SecurityBundle\Message\SendResetPasswordCommand;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;
use Xutim\SecurityBundle\Security\UserInterface;

readonly class SendResetPasswordHandler implements CommandHandlerInterface
{
    /**
     * @param UserRepositoryInterface&ServiceEntityRepository<UserInterface> $userRepository
     */
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        private UrlGeneratorInterface $urlGenerator,
        private SiteContext $siteContext
    ) {
    }

    public function __invoke(SendResetPasswordCommand $command): void
    {
        $user = $this->userRepository->findById($command->id);
        if ($user === null) {
            throw new LogicException('User couldn\'t be found');
        }

        $resetToken = $this->resetPasswordHelper->generateResetToken($user);

        $email = (new TemplatedEmail())
            ->from(new Address($this->siteContext->getSender(), 'Taizé Website'))
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('@XutimSecurity/security/reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ]);

        $this->logger->error(sprintf('[User password request token] %s link: %s', $user->getEmail(), $this->urlGenerator->generate('admin_reset_password', ['token' => $resetToken->getToken()])));

        $this->mailer->send($email);
    }
}
