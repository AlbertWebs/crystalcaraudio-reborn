<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreOrder extends Model
{
    protected $guarded = [];
    protected $casts = ['items' => 'array', 'econfirm_webhook_secret' => 'encrypted'];
}
