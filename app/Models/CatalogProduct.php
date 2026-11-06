<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogProduct extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['gallery' => 'array', 'specifications' => 'array', 'rating' => 'float', 'review_count' => 'integer'];
    }
}
