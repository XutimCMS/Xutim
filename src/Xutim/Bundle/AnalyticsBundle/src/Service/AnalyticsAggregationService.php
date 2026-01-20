<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Service;

use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyCountryRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyDeviceRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyPageReferrerRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailySessionRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailySummaryRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyTrafficSourceRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsDailyUtmRepositoryInterface;
use Xutim\AnalyticsBundle\Domain\Repository\AnalyticsEventRepositoryInterface;

final class AnalyticsAggregationService
{
    public function __construct(
        private readonly AnalyticsEventRepositoryInterface $eventRepository,
        private readonly AnalyticsDailySummaryRepositoryInterface $summaryRepository,
        private readonly AnalyticsDailyPageReferrerRepositoryInterface $pageReferrerRepository,
        private readonly AnalyticsDailyTrafficSourceRepositoryInterface $trafficSourceRepository,
        private readonly AnalyticsDailyCountryRepositoryInterface $countryRepository,
        private readonly AnalyticsDailyDeviceRepositoryInterface $deviceRepository,
        private readonly AnalyticsDailyUtmRepositoryInterface $utmRepository,
        private readonly AnalyticsDailySessionRepositoryInterface $sessionRepository,
        private readonly UserAgentParser $userAgentParser,
        private readonly ReferrerParser $referrerParser,
        private readonly ?string $siteHost = null,
    ) {
    }

    public function countEventsForDate(\DateTimeImmutable $date): int
    {
        return $this->eventRepository->countEventsForDate($date);
    }

    public function aggregateDailySummary(\DateTimeImmutable $date): int
    {
        $this->summaryRepository->deleteByDate($date);

        $data = $this->eventRepository->getDailySummaryAggregation($date);

        return $this->summaryRepository->insertAggregatedData($date, $data);
    }

    public function aggregateDailyTrafficSource(\DateTimeImmutable $date): int
    {
        $this->trafficSourceRepository->deleteByDate($date);

        $events = $this->eventRepository->getEventsForTrafficSourceAggregation($date);

        $sources = [];
        foreach ($events as $event) {
            $source = $this->detectSource($event['referer']);
            $sessionBucket = $event['sessionBucket'];

            if (!isset($sources[$source])) {
                $sources[$source] = ['visits' => 0, 'sessions' => []];
            }
            $sources[$source]['visits']++;
            $sources[$source]['sessions'][$sessionBucket] = true;
        }

        $data = [];
        foreach ($sources as $source => $sourceData) {
            $data[] = [
                'source' => $source,
                'visits' => $sourceData['visits'],
                'uniqueVisitors' => count($sourceData['sessions']),
            ];
        }

        return $this->trafficSourceRepository->insertAggregatedData($date, $data);
    }

    public function aggregateDailyCountry(\DateTimeImmutable $date): int
    {
        $this->countryRepository->deleteByDate($date);

        $data = $this->eventRepository->getDailyCountryAggregation($date);

        return $this->countryRepository->insertAggregatedData($date, $data);
    }

    public function aggregateDailyDevice(\DateTimeImmutable $date): int
    {
        $this->deviceRepository->deleteByDate($date);

        $events = $this->eventRepository->getEventsForDeviceAggregation($date);

        $devices = [];
        foreach ($events as $event) {
            $ua = $event['userAgent'];
            $sessionBucket = $event['sessionBucket'];

            $deviceType = $this->userAgentParser->parseDeviceCategory($ua);
            $browser = $this->userAgentParser->parseBrowser($ua);
            $os = $this->userAgentParser->parseOs($ua);

            $key = $deviceType . '|' . $browser . '|' . $os;

            if (!isset($devices[$key])) {
                $devices[$key] = [
                    'deviceType' => $deviceType,
                    'browser' => $browser,
                    'os' => $os,
                    'visits' => 0,
                    'sessions' => [],
                ];
            }
            $devices[$key]['visits']++;
            $devices[$key]['sessions'][$sessionBucket] = true;
        }

        $data = [];
        foreach ($devices as $device) {
            $data[] = [
                'deviceType' => $device['deviceType'],
                'browser' => $device['browser'],
                'os' => $device['os'],
                'visits' => $device['visits'],
                'uniqueVisitors' => count($device['sessions']),
            ];
        }

        return $this->deviceRepository->insertAggregatedData($date, $data);
    }

    public function aggregateDailyUtm(\DateTimeImmutable $date): int
    {
        $this->utmRepository->deleteByDate($date);

        $data = $this->eventRepository->getDailyUtmAggregation($date);

        return $this->utmRepository->insertAggregatedData($date, $data);
    }

    public function aggregateDailyPageReferrer(\DateTimeImmutable $date): int
    {
        $this->pageReferrerRepository->deleteByDate($date);

        $events = $this->eventRepository->getEventsForPageReferrerAggregation($date);

        $referrers = [];
        $siteHost = $this->siteHost ?? '';

        foreach ($events as $event) {
            $targetPath = $event['path'];
            $referer = $event['referer'];
            $sessionBucket = $event['sessionBucket'];

            $isExternal = $siteHost !== '' && $this->referrerParser->isExternal($referer, $siteHost);
            $referrerKey = $isExternal
                ? ($this->referrerParser->extractDomain($referer) ?? 'unknown')
                : ($this->referrerParser->extractPath($referer) ?? '/');

            $key = $targetPath . '|' . $referrerKey . '|' . ($isExternal ? '1' : '0');

            if (!isset($referrers[$key])) {
                $referrers[$key] = [
                    'targetPath' => $targetPath,
                    'referrer' => $referrerKey,
                    'isExternal' => $isExternal,
                    'visits' => 0,
                    'sessions' => [],
                ];
            }
            $referrers[$key]['visits']++;
            $referrers[$key]['sessions'][$sessionBucket] = true;
        }

        $data = [];
        foreach ($referrers as $referrer) {
            if ($referrer['visits'] < 2) {
                continue;
            }

            $data[] = [
                'targetPath' => $referrer['targetPath'],
                'referrer' => $referrer['referrer'],
                'isExternal' => $referrer['isExternal'],
                'visits' => $referrer['visits'],
                'uniqueVisitors' => count($referrer['sessions']),
            ];
        }

        return $this->pageReferrerRepository->insertAggregatedData($date, $data);
    }

    public function aggregateDailySession(\DateTimeImmutable $date): int
    {
        $this->sessionRepository->deleteByDate($date);

        $events = $this->eventRepository->getEventsForSessionAggregation($date);

        $sessions = [];
        foreach ($events as $event) {
            $bucket = $event['sessionBucket'];
            if (!isset($sessions[$bucket])) {
                $sessions[$bucket] = [];
            }
            $sessions[$bucket][] = [
                'path' => $event['path'],
                'time' => $event['recordedAt'],
            ];
        }

        $aggregated = [];
        foreach ($sessions as $pages) {
            $pageCount = count($pages);
            $entryPath = $pages[0]['path'];
            $exitPath = $pages[$pageCount - 1]['path'];
            $isBounce = $pageCount === 1;

            $firstTime = $pages[0]['time'];
            $lastTime = $pages[$pageCount - 1]['time'];
            $durationSeconds = $lastTime->getTimestamp() - $firstTime->getTimestamp();

            $key = $entryPath . '|' . $exitPath;
            if (!isset($aggregated[$key])) {
                $aggregated[$key] = [
                    'entryPath' => $entryPath,
                    'exitPath' => $exitPath,
                    'sessionCount' => 0,
                    'totalPageviews' => 0,
                    'bounces' => 0,
                    'totalDurationSeconds' => 0,
                ];
            }
            $aggregated[$key]['sessionCount']++;
            $aggregated[$key]['totalPageviews'] += $pageCount;
            $aggregated[$key]['bounces'] += $isBounce ? 1 : 0;
            $aggregated[$key]['totalDurationSeconds'] += $durationSeconds;
        }

        $data = [];
        foreach ($aggregated as $session) {
            if ($session['sessionCount'] < 2) {
                continue;
            }

            $data[] = $session;
        }

        return $this->sessionRepository->insertAggregatedData($date, $data);
    }

    /**
     * @return array<string, int>
     */
    public function aggregateAll(\DateTimeImmutable $date): array
    {
        return [
            'summary' => $this->aggregateDailySummary($date),
            'traffic_source' => $this->aggregateDailyTrafficSource($date),
            'country' => $this->aggregateDailyCountry($date),
            'device' => $this->aggregateDailyDevice($date),
            'utm' => $this->aggregateDailyUtm($date),
            'page_referrer' => $this->aggregateDailyPageReferrer($date),
            'session' => $this->aggregateDailySession($date),
        ];
    }

    private function detectSource(?string $referer): string
    {
        if ($referer === null || $referer === '') {
            return 'Direct';
        }

        return $this->referrerParser->detectSourceFromReferrer($referer) ?? 'Direct';
    }
}
