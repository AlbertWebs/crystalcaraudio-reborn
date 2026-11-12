<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortfolioProject extends Model
{
    protected $fillable = [
        'legacy_source_id',
        'title',
        'slug',
        'vehicle_make',
        'vehicle_model',
        'vehicle_year',
        'work_order',
        'upgrade_type',
        'project_date',
        'service_area',
        'client_name',
        'description',
        'related_product_slugs',
        'related_category_slugs',
        'images',
        'video_path',
        'is_published',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'project_date' => 'date:Y-m-d',
            'images' => 'array',
            'related_product_slugs' => 'array',
            'related_category_slugs' => 'array',
            'is_published' => 'boolean',
        ];
    }
}
