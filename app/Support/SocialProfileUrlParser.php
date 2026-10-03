<?php

namespace App\Support;

class SocialProfileUrlParser
{
    public static function detectPlatform(string $url): ?string
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        return match (true) {
            str_contains($host, 'tiktok.com') => 'tiktok',
            str_contains($host, 'instagram.com'), str_contains($host, 'instagr.am') => 'instagram',
            str_contains($host, 'facebook.com'), str_contains($host, 'fb.com'), str_contains($host, 'fb.watch') => 'facebook',
            str_contains($host, 'youtube.com'), str_contains($host, 'youtu.be') => 'youtube',
            default => null,
        };
    }

    public static function extractHandle(string $url, string $platform): ?string
    {
        $path = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');

        return match ($platform) {
            'tiktok' => self::matchSegment($path, '/@?([^\/]+)/'),
            'instagram' => self::matchSegment($path, '/([^\/\?]+)/'),
            'facebook' => self::matchSegment($path, '/([^\/\?]+)/'),
            'youtube' => self::matchSegment($path, '/@([^\/\?]+)/') ?? self::matchSegment($path, '/channel\/([^\/\?]+)/'),
            default => null,
        };
    }

    private static function matchSegment(string $path, string $pattern): ?string
    {
        if (preg_match($pattern, $path, $matches)) {
            return '@'.ltrim($matches[1], '@');
        }

        return null;
    }
}
