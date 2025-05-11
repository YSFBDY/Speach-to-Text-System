<?php

namespace App\Traits;

use Carbon\Carbon;

trait TimeAgo
{
    public function getTimeAgo($timestamp)
    {
        $time = Carbon::parse($timestamp);
        $now = now();

        // Ensure we don't get negative values
        if ($time->greaterThan($now)) {
            return '0m'; // Future timestamps are treated as "just now"
        }

        $diffInMinutes = (int)max(0, $time->diffInMinutes($now)); // Prevent negatives
        $diffInHours = (int)max(0, $time->diffInHours($now));
        $diffInDays = (int)max(0, $time->diffInDays($now));
        $diffInWeeks = (int)max(0, $time->diffInWeeks($now));
        $diffInMonths = (int)max(0, $time->diffInMonths($now));

        if ($diffInMinutes < 60) {
            return $diffInMinutes . 'm';
        } elseif ($diffInHours < 24) {
            return $diffInHours . 'h';
        } elseif ($diffInDays < 7) {
            return $diffInDays . 'd';
        } elseif ($diffInWeeks < 4) {
            return $diffInWeeks . 'w';
        } else {
            return $diffInMonths . 'mo';
        }
    }
}