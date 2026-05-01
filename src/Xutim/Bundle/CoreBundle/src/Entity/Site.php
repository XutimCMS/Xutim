<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Domain\Model\SiteInterface;
use Xutim\CoreBundle\Dto\SiteDto;

#[MappedSuperclass]
class Site implements SiteInterface
{
    public const int DEFAULT_UNTRANSLATED_ARTICLE_AGE_LIMIT_DAYS = 180;
    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    /** @var array<string> */
    #[Column(type: 'json', nullable: false, options: ['comment' => 'Site\'s languages.'])]
    private array $locales;

    /** @var array<string> */
    #[Column(type: 'json', nullable: false, options: ['comment' => 'Site\'s extended content languages.'])]
    private array $extendedContentLocales;

    /** @var array<string> */
    #[Column(type: 'json', nullable: false, options: ['default' => '[]', 'comment' => 'Email addresses receiving admin alert notifications.'])]
    private array $adminAlertEmails;

    #[Column(type: 'string', length: 255, nullable: false, options: ['comment' => 'Site\'s public theme.'])]
    private string $theme;

    #[Column(type: 'string', length: 255, nullable: false, options: ['comment' => 'Site\'s sender\'s email address.'])]
    private string $sender;

    #[Column(type: 'string', length: 10, nullable: false, options: ['comment' => 'Site\'s reference locale for translations.'])]
    private string $referenceLocale;

    #[Column(type: 'integer', nullable: false, options: ['default' => self::DEFAULT_UNTRANSLATED_ARTICLE_AGE_LIMIT_DAYS, 'comment' => 'Max age in days for untranslated articles on dashboard. 0 = no limit.'])]
    private int $untranslatedArticleAgeLimitDays = self::DEFAULT_UNTRANSLATED_ARTICLE_AGE_LIMIT_DAYS;

    #[ManyToOne(targetEntity: PageInterface::class)]
    #[JoinColumn(name: 'homepage_id', nullable: true)]
    private ?PageInterface $homepage = null;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->locales = ['en', 'fr'];
        $this->extendedContentLocales = ['en', 'fr'];
        $this->adminAlertEmails = [];
        $this->theme = 'default';
        $this->sender = 'website@example.com';
        $this->referenceLocale = 'en';
        $this->untranslatedArticleAgeLimitDays = self::DEFAULT_UNTRANSLATED_ARTICLE_AGE_LIMIT_DAYS;
    }

    /**
     * @param array<string> $locales
     * @param array<string> $extendedContentLocales
     * @param array<string> $adminAlertEmails
     */
    public function change(
        array $locales,
        array $extendedContentLocales,
        string $theme,
        string $sender,
        string $referenceLocale,
        int $untranslatedArticleAgeLimitDays,
        ?PageInterface $homepage,
        array $adminAlertEmails = [],
    ): void {
        usort($locales, fn ($l1, $l2) => Languages::getName($l1) <=> Languages::getName($l2));
        usort($extendedContentLocales, fn ($l1, $l2) => Languages::getName($l1) <=> Languages::getName($l2));
        $this->locales = $locales;
        $this->extendedContentLocales = $extendedContentLocales;
        $this->theme = $theme;
        $this->sender = $sender;
        $this->referenceLocale = $referenceLocale;
        $this->untranslatedArticleAgeLimitDays = $untranslatedArticleAgeLimitDays;
        $this->homepage = $homepage;
        $this->adminAlertEmails = self::normalizeEmails($adminAlertEmails);
    }

    /**
     * @param array<string> $emails
     *
     * @return array<string>
     */
    private static function normalizeEmails(array $emails): array
    {
        $normalized = [];
        foreach ($emails as $email) {
            $email = strtolower(trim($email));
            if ($email === '') {
                continue;
            }
            $normalized[$email] = $email;
        }

        return array_values($normalized);
    }

    public function getHomepage(): ?PageInterface
    {
        return $this->homepage;
    }

    public function changeHomepage(?PageInterface $homepage): void
    {
        $this->homepage = $homepage;
    }

    public function getUntranslatedArticleAgeLimitDays(): int
    {
        return $this->untranslatedArticleAgeLimitDays;
    }

    public function getReferenceLocale(): string
    {
        return $this->referenceLocale;
    }

    /**
     * @return array<string>
     */
    public function getLocales(): array
    {
        return $this->locales;
    }

    /**
     * @return array<string>
     */
    public function getContentLocales(): array
    {
        return $this->extendedContentLocales;
    }

    public function getSender(): string
    {
        return $this->sender;
    }

    /**
     * @return array<string>
     */
    public function getAdminAlertEmails(): array
    {
        return $this->adminAlertEmails;
    }

    public function toDto(): SiteDto
    {
        return new SiteDto(
            $this->locales,
            $this->extendedContentLocales,
            $this->theme,
            $this->sender,
            $this->referenceLocale,
            $this->untranslatedArticleAgeLimitDays,
            $this->homepage?->getId()->toRfc4122(),
            $this->adminAlertEmails,
        );
    }
}
