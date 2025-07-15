<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProject extends Model
{
    protected $fillable = [
        'company_id',
        'title',
        'image_url',
        'description',
        'start_date',
        'end_date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function getImageUrlAttribute($value)
    {
        return url($value);
    }

   

}
