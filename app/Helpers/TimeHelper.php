<?php

if (! function_exists('convert_seconds_to_duration')) {
    /**
     * Convert seconds to a human-readable duration format (e.g., "1 Jam 30 Menit").
     *
     * @param  int  $seconds
     * @return string
     */
    function convert_seconds_to_duration($seconds)
    {
        if ($seconds < 60) {
            return $seconds.' Detik';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        $parts = [];

        if ($hours > 0) {
            $parts[] = $hours.' Jam';
        }

        if ($minutes > 0) {
            $parts[] = $minutes.' Menit';
        }

        return implode(' ', $parts);
    }
}
