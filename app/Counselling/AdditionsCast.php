<?php

namespace App\Counselling;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class AdditionsCast implements CastsAttributes
{
    public function get($model, $key, $value, $attributes)
    {
        return json_decode($value, true);
    }

    public function set($model, $key, $value, $attributes)
    {
        // make sure that additions json has always same order, regardless which was submitted first
        $orderedAdditions = [
            'rating' => [
                'value' => $value['rating']['value'] ?? '',
                'explanation' => $value['rating']['explanation'] ?? '',
            ],
            'note' => $value['note'] ?? '',
        ];

        return json_encode($orderedAdditions);
    }
}
