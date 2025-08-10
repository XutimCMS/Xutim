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

    #[Column(type: 'string')]
    private readonly string $path;

    #[Column(type: 'string', nullable: true)]
    private readonly ?string $referer;

    #[Column(type: 'string', nullable: true)]
    private readonly ?string $userAgent;

    #[Column(type: 'string', nullable: true)]
    private readonly ?string $language;

    #[Column(type: 'string', nullable: true)]
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

    #[Column(type: 'string', nullable: true)]
    private readonly ?string $anonymizedIp;

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
        ?string $anonymizedIp
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
    }
}
