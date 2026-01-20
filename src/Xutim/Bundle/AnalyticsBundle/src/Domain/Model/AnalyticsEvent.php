<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Model;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Uid\Uuid;

#[MappedSuperclass]
class AnalyticsEvent implements AnalyticsEventInterface
{
    #[Id]
    #[Column(type: 'uuid')]
    private readonly Uuid $id;

    #[Column(type: 'text')]
    private readonly string $path;

    #[Column(type: 'text', nullable: true)]
    private readonly ?string $referer;

    #[Column(type: 'text', nullable: true)]
    private readonly ?string $userAgent;

    #[Column(type: 'string', nullable: true)]
    private readonly ?string $language;

    #[Column(type: 'string', length: 32, nullable: true)]
    private readonly ?string $screenSize;

    #[Column(type: 'integer', nullable: true)]
    private readonly ?int $loadTimeMs;

    #[Column(type: 'integer', nullable: true)]
    private readonly ?int $scrollDepth;

    /**
     * @var list<array{
     *     tag: string,
     *     id?: string,
     *     class?: string,
     *     role?: string,
     *     text?: string
     * }>
     */
    #[Column(type: 'json', nullable: true)]
    private readonly array $clickedElements;

    #[Column(type: 'string', length: 2, nullable: true)]
    private readonly ?string $country;

    #[Column(type: 'boolean')]
    private readonly bool $isBot;

    #[Column(type: 'datetime_immutable')]
    private readonly \DateTimeImmutable $recordedAt;

    #[Column(type: 'string', length: 128, nullable: true)]
    private readonly ?string $anonymizedIp;

    #[Column(type: 'string', length: 128)]
    private readonly string $sessionBucket;

    #[Column(type: 'string', length: 255, nullable: true)]
    private readonly ?string $utmSource;

    #[Column(type: 'string', length: 255, nullable: true)]
    private readonly ?string $utmMedium;

    #[Column(type: 'string', length: 255, nullable: true)]
    private readonly ?string $utmCampaign;

    /**
     * @param list<array{
     *     tag: string,
     *     id?: string,
     *     class?: string,
     *     role?: string,
     *     text?: string
     * }> $clickedElements
     */
    public function __construct(
        string $path,
        ?string $referer,
        ?string $userAgent,
        ?string $language,
        ?string $screenSize,
        ?int $loadTimeMs,
        ?int $scrollDepth,
        array $clickedElements,
        ?string $country,
        bool $isBot,
        ?string $anonymizedIp,
        string $sessionBucket,
        ?string $utmSource = null,
        ?string $utmMedium = null,
        ?string $utmCampaign = null
    ) {
        $this->id = Uuid::v4();
        $this->path = $path;
        $this->referer = $referer;
        $this->userAgent = $userAgent;
        $this->language = $language;
        $this->screenSize = $screenSize;
        $this->loadTimeMs = $loadTimeMs;
        $this->scrollDepth = $scrollDepth;
        $this->clickedElements = $clickedElements;
        $this->country = $country;
        $this->isBot = $isBot;
        $this->recordedAt = new \DateTimeImmutable();
        $this->anonymizedIp = $anonymizedIp;
        $this->sessionBucket = $sessionBucket;
        $this->utmSource = $utmSource;
        $this->utmMedium = $utmMedium;
        $this->utmCampaign = $utmCampaign;
    }

    // Getters for the properties
    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getPath(): string
    {
        return $this->path;
    }
    public function getReferer(): ?string
    {
        return $this->referer;
    }
    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }
    public function getLanguage(): ?string
    {
        return $this->language;
    }
    public function getScreenSize(): ?string
    {
        return $this->screenSize;
    }
    public function getLoadTimeMs(): ?int
    {
        return $this->loadTimeMs;
    }
    public function getScrollDepth(): ?int
    {
        return $this->scrollDepth;
    }

    /**
     * @return list<array{
     *     tag: string,
     *     id?: string,
     *     class?: string,
     *     role?: string,
     *     text?: string
     * }>
     */
    public function getClickedElements(): array
    {
        return $this->clickedElements;
    }
    public function getCountry(): ?string
    {
        return $this->country;
    }
    public function isBot(): bool
    {
        return $this->isBot;
    }
    public function getRecordedAt(): \DateTimeImmutable
    {
        return $this->recordedAt;
    }
    public function getAnonymizedIp(): ?string
    {
        return $this->anonymizedIp;
    }

    public function getSessionBucket(): string
    {
        return $this->sessionBucket;
    }

    public function getUtmSource(): ?string
    {
        return $this->utmSource;
    }

    public function getUtmMedium(): ?string
    {
        return $this->utmMedium;
    }

    public function getUtmCampaign(): ?string
    {
        return $this->utmCampaign;
    }
}
