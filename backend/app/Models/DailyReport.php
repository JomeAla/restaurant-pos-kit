<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    protected $fillable = ['report_date', 'total_orders', 'total_revenue', 'total_tax', 'total_discounts', 'total_cancelled', 'payment_breakdown', 'peak_hours'];

    protected $casts = ['report_date' => 'date', 'payment_breakdown' => 'array', 'peak_hours' => 'array'];
}
