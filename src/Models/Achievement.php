<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;
use DateTimeZone;

final class Achievement
{
    public function __construct(
        private readonly string $apiName,
        private readonly string $displayName = '',
        private readonly string $description = '',
        private readonly string $iconUrl = '',
        private readonly string $iconGrayUrl = '',
        private readonly bool $hidden = false,
        private bool $achieved = false,
        private ?int $unlockTime = null,
    ) {
    }

    public static function fromSteam(array $schema, array $player = []): self
    {
        $apiName = (string) ($schema['name'] ?? $player['apiname'] ?? '');
        $displayName = (string) ($schema['displayName'] ?? '');
        $unlockTime = (int) ($player['unlocktime'] ?? 0);

        return new self(
            $apiName,
            $displayName !== '' ? $displayName : $apiName,
            (string) ($schema['description'] ?? ''),
            (string) ($schema['icon'] ?? ''),
            (string) ($schema['icongray'] ?? ''),
            (bool) ($schema['hidden'] ?? false),
            (bool) ($player['achieved'] ?? false),
            $unlockTime > 0 ? $unlockTime : null,
        );
    }

    public function getApiName(): string
    {
        return $this->apiName;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
    public function getDisplayDescription(): string
    {
        if ($this->hidden && ! $this->achieved) {
            return 'Succes cache';
        }

        return $this->description !== '' ? $this->description : 'Aucune description.';
    }

    public function getIconUrl(): string
    {
        return $this->achieved ? $this->iconUrl : $this->iconGrayUrl;
    }

    public function getIconGrayUrl(): string
    {
        return $this->iconGrayUrl;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function isAchieved(): bool
    {
        return $this->achieved;
    }

    public function getUnlockTime(): ?int
    {
        return $this->unlockTime;
    }

    public function getUnlockDate(string $format = 'd/m/Y H:i'): ?string
    {
        if ($this->unlockTime === null) {
            return null;
        }

        $date = (new DateTimeImmutable('@' . $this->unlockTime))
            ->setTimezone(new DateTimeZone(date_default_timezone_get()));

        return $date->format($format);
    }
}
