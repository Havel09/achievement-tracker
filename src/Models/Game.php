<?php

declare(strict_types=1);

namespace App\Models;

final class Game
{
    private int $achievementsTotal = 0;

    private int $achievementsUnlocked = 0;

    public function __construct(
        private readonly int $appId,
        private readonly string $name,
        private readonly int $playtimeForever = 0,
        private readonly ?string $iconUrl = null,
    ) {
    }

    public static function fromSteamOwnedGame(array $data): self
    {
        $appId = (int) ($data['appid'] ?? 0);
        $iconHash = isset($data['img_icon_url']) ? (string) $data['img_icon_url'] : null;

        return new self(
            $appId,
            (string) ($data['name'] ?? 'Jeu inconnu'),
            (int) ($data['playtime_forever'] ?? 0),
            self::buildIconUrl($appId, $iconHash),
        );
    }

    private static function buildIconUrl(int $appId, ?string $iconHash): ?string
    {
        if ($appId <= 0 || $iconHash === null || $iconHash === '') {
            return null;
        }

        return sprintf(
            'https://media.steampowered.com/steamcommunity/public/images/apps/%d/%s.jpg',
            $appId,
            $iconHash,
        );
    }

    public function getAppId(): int
    {
        return $this->appId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPlaytimeForever(): int
    {
        return $this->playtimeForever;
    }

    public function getPlaytimeHours(): float
    {
        return round($this->playtimeForever / 60, 1);
    }

    public function getIconUrl(): ?string
    {
        return $this->iconUrl;
    }

    public function getStoreUrl(): string
    {
        return 'https://store.steampowered.com/app/' . $this->appId;
    }

    public function getAchievementsTotal(): int
    {
        return $this->achievementsTotal;
    }

    public function getAchievementsUnlocked(): int
    {
        return $this->achievementsUnlocked;
    }

    public function setAchievementProgress(int $total, int $unlocked): void
    {
        $this->achievementsTotal = max(0, $total);
        $this->achievementsUnlocked = min(max(0, $unlocked), $this->achievementsTotal);
    }

    public function hasAchievements(): bool
    {
        return $this->achievementsTotal > 0;
    }

    public function getCompletionPercentage(): float
    {
        if ($this->achievementsTotal === 0) {
            return 0.0;
        }

        return round($this->achievementsUnlocked / $this->achievementsTotal * 100, 1);
    }

    public function isCompleted(): bool
    {
        return $this->achievementsTotal > 0
            && $this->achievementsUnlocked === $this->achievementsTotal;
    }
}
