<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CMS extends Model
{
    protected $fillable = [
        'page',
        'section',
        'name',
        'slug',
        'title',
        'sub_title',
        'description',
        'sub_description',
        'bg',
        'image',
        'btn_text',
        'btn_link',
        'btn_color',
        'metadata',
        'status',
    ];


    protected $casts = [
        'metadata' => 'array',
    ];

    public function getImageAttribute($value): string | null
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        // Check if the request is an API request
        if (request()->is('api/*') && !empty($value)) {
            // Return the full URL for API requests
            return url($value);
        }

        // Return only the path for web requests
        return $value;
    }

    public function getBgAttribute($value): string | null
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        // Check if the request is an API request
        if (request()->is('api/*') && !empty($value)) {
            // Return the full URL for API requests
            return url($value);
        }

        // Return only the path for web requests
        return $value;
    }
}
