<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    // month tiene formato YYYY-MM (ej: 2026-11)
    protected $fillable = ['user_id', 'category_id', 'month', 'amount_limit'];
}
