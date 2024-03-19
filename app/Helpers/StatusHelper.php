<?php

namespace App\Helpers;

use App\Models\Status;

class StatusHelper
{
    /**
     * returns status->title for given id
     */
    public static function getStatusById($status_id) {
        $status = Status::find($status_id);
        return $status ? $status->title : null;
    }

    /**
     * returns status->id for given title
     */
    public static function getIdFromTitle($title) {
        $status = Status::where('title', $title)->first();
        return $status ? $status->id : null;
    }
}
