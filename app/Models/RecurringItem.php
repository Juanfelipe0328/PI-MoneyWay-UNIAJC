<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecurringItem extends Model
{
    // start_month / end_month tienen formato YYYY-MM
    protected $fillable = [
        'user_id', 'category_id', 'type', 'amount', 'description', 'custom_category',
        'day_of_month', 'start_month', 'end_month', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];
}
