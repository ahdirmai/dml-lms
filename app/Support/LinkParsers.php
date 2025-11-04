<?php

namespace App\Support;

class LinkParsers
{
    public static function parseYouTubeId(string $url): ?string
    {
        if (preg_match('~youtu\.be/([A-Za-z0-9_-]{6,})~', $url, $m)) return $m[1];
        $query = parse_url($url, PHP_URL_QUERY) ?? '';
        if (preg_match('~(?:^|&)v=([A-Za-z0-9_-]{6,})~', $query, $m)) return $m[1];
        if (preg_match('~/embed/([A-Za-z0-9_-]{6,})~', $url, $m)) return $m[1];
        return null;
    }

    public static function parseGDriveFileId(string $url): ?string
    {
        if (preg_match('~/file/d/([A-Za-z0-9_-]{10,})/~', $url, $m)) return $m[1];
        if (preg_match('~[?&]id=([A-Za-z0-9_-]{10,})~', $url, $m)) return $m[1];
        if (preg_match('~/uc\?id=([A-Za-z0-9_-]{10,})~', $url, $m)) return $m[1];
        return null;
    }
}
