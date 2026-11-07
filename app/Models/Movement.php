<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'type', 'amount', 
        'movement_date', 'merchant', 'description', 'source', 'raw_message'
    ];
}