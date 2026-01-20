<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Domain\Model;

use Symfony\Component\Uid\Uuid;

interface AnalyticsEventInterface
{
    public function getId(): Uuid;

    public function getPath(): string;

    public function getReferer(): ?string;

    public function getUserAgent(): ?string;

    public function getLanguage(): ?string;

    public function getScreenSize(): ?string;

    public function getLoadTimeMs(): ?int;

    public function getScrollDepth(): ?int;

    /**
     * @return list<array{
     *     tag: string,
     *     id?: string,
     *     class?: string,
     *     role?: string,
     *     text?: string
     * }>
     */
    public function getClickedElements(): array;

    public function getCountry(): ?string;

    public function isBot(): bool;

    public function getRecordedAt(): \DateTimeImmutable;

    public function getAnonymizedIp(): ?string;

    public function getSessionBucket(): string;

    public function getUtmSource(): ?string;

    public function getUtmMedium(): ?string;

    public function getUtmCampaign(): ?string;
}
