<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WidgetWidgetUser extends Model
{
    use HasFactory;

    protected $table = 'widget_widget_user';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'widget_id', 'widget_user_id',
    ];

    // Add relationships or additional methods if needed
}
