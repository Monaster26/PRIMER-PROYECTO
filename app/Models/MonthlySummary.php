<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlySummary extends Model
{
    protected $fillable = [
        'year',
        'month',
        'total_revenue',
        'total_cost_of_goods',
        'operating_expenses',
    ];
}
