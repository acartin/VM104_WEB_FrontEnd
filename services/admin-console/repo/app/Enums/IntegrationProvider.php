<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum IntegrationProvider: string implements HasLabel, HasColor
{
    case Meta = 'meta';
    case Google = 'google';
    case Amazon = 'amazon';
    case TikTok = 'tiktok';
    case LinkedIn = 'linkedin';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Meta => 'Meta (Facebook/Instagram)',
            self::Google => 'Google Ads',
            self::Amazon => 'Amazon Ads',
            self::TikTok => 'TikTok Ads',
            self::LinkedIn => 'LinkedIn Ads',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Meta => 'info',
            self::Google => 'success',
            self::Amazon => 'warning',
            self::TikTok => 'danger',
            self::LinkedIn => 'primary',
        };
    }
}
