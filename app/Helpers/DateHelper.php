<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper {
    public static function formatDate($date) {
        return Carbon::parse($date)->toDateString();
    }
}